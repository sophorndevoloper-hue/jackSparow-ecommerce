<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserHistoryController extends Controller
{
    /**
     * Display the administrative user activity audit log history.
     */
    public function index(Request $request): View
    {
        $query = UserActivity::query()->with('user');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('entity_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $activities = $query->latest('id')->paginate(20)->withQueryString();

        // Audit KPI Metrics
        $totalActivities = UserActivity::count();
        $todayActivities = UserActivity::whereDate('created_at', Carbon::today())->count();
        $modificationsCount = UserActivity::whereIn('action', ['CREATE', 'UPDATE', 'DELETE'])->count();
        $authEventsCount = UserActivity::whereIn('action', ['LOGIN', 'LOGOUT'])->count();
        $activeUserCount = UserActivity::distinct('user_id')->whereNotNull('user_id')->count('user_id');

        // Filter Options
        $users = User::orderBy('name')->get(['id', 'name', 'email']);
        $distinctActions = UserActivity::select('action')->distinct()->pluck('action')->filter()->values();
        $distinctModules = UserActivity::select('module')->distinct()->pluck('module')->filter()->values();

        return view('backend.settings.user-history.index', compact(
            'activities',
            'totalActivities',
            'todayActivities',
            'modificationsCount',
            'authEventsCount',
            'activeUserCount',
            'users',
            'distinctActions',
            'distinctModules'
        ));
    }

    /**
     * Get specific activity log detail as JSON for inspection modal.
     */
    public function show(UserActivity $activity): JsonResponse
    {
        return response()->json([
            'id' => $activity->id,
            'user' => [
                'name' => $activity->user_name,
                'email' => $activity->user_email,
            ],
            'action' => $activity->action,
            'module' => $activity->module,
            'entity_id' => $activity->entity_id,
            'description' => $activity->description,
            'properties' => $activity->properties,
            'ip_address' => $activity->ip_address,
            'user_agent' => $activity->user_agent,
            'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
            'time_ago' => $activity->created_at->diffForHumans(),
        ]);
    }

    /**
     * Clear or prune old activity logs.
     */
    public function clear(Request $request): RedirectResponse
    {
        $days = (int) $request->input('days', 30);

        if ($days <= 0) {
            $deleted = UserActivity::query()->delete();
            $msg = "Cleared all {$deleted} user activity audit logs.";
        } else {
            $threshold = Carbon::now()->subDays($days);
            $deleted = UserActivity::where('created_at', '<', $threshold)->delete();
            $msg = "Pruned {$deleted} user activity audit logs older than {$days} days.";
        }

        UserActivity::log(
            'DELETE',
            'UserHistory',
            $msg
        );

        return back()->with('success', "Successfully pruned {$deleted} audit activity logs.");
    }
}
