<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryAgent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeliveryAgentController extends Controller
{
    /**
     * Display a listing of delivery agents.
     */
    public function index(Request $request): View
    {
        $query = DeliveryAgent::query()->withCount('deliveries');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('zone', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->input('vehicle_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $agents = $query->latest('id')->paginate(15)->withQueryString();

        // KPI Counts
        $totalAgents = DeliveryAgent::count();
        $availableCount = DeliveryAgent::where('is_active', true)->where('status', 'available')->count();
        $onDeliveryCount = DeliveryAgent::where('is_active', true)->where('status', 'on_delivery')->count();
        $inactiveCount = DeliveryAgent::where(function ($q) {
            $q->where('is_active', false)->orWhere('status', 'inactive');
        })->count();
        $totalCompletedDeliveries = Delivery::whereNotNull('delivery_agent_id')->where('status', 'delivered')->count();

        return view('backend.delivery-agents.index', compact(
            'agents',
            'totalAgents',
            'availableCount',
            'onDeliveryCount',
            'inactiveCount',
            'totalCompletedDeliveries'
        ));
    }

    /**
     * Store a newly created delivery agent in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:delivery_agents,code'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'vehicle_type' => ['required', 'in:motorcycle,van,truck,bicycle,car,other'],
            'license_plate' => ['nullable', 'string', 'max:50'],
            'zone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:available,on_delivery,inactive'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $agent = DeliveryAgent::create($validated);

        return redirect()->route('admin.delivery-agents.index')
            ->with('success', "Delivery Agent '{$agent->name}' ({$agent->code}) registered successfully.");
    }

    /**
     * Update the specified delivery agent in storage.
     */
    public function update(Request $request, DeliveryAgent $deliveryAgent): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('delivery_agents', 'code')->ignore($deliveryAgent->id)],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'vehicle_type' => ['required', 'in:motorcycle,van,truck,bicycle,car,other'],
            'license_plate' => ['nullable', 'string', 'max:50'],
            'zone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:available,on_delivery,inactive'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $deliveryAgent->update($validated);

        return redirect()->route('admin.delivery-agents.index')
            ->with('success', "Delivery Agent '{$deliveryAgent->name}' updated successfully.");
    }

    /**
     * Toggle the active status of a delivery agent.
     */
    public function toggleActive(DeliveryAgent $deliveryAgent): RedirectResponse
    {
        $deliveryAgent->is_active = ! $deliveryAgent->is_active;
        $deliveryAgent->save();

        $status = $deliveryAgent->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Delivery Agent '{$deliveryAgent->name}' has been {$status}.");
    }

    /**
     * Remove the specified delivery agent from storage.
     */
    public function destroy(DeliveryAgent $deliveryAgent): RedirectResponse
    {
        $hasActiveDeliveries = $deliveryAgent->deliveries()
            ->whereIn('status', ['pending', 'preparing', 'dispatched', 'in_transit', 'out_for_delivery'])
            ->exists();

        if ($hasActiveDeliveries) {
            return back()->with('error', "Cannot delete '{$deliveryAgent->name}' because they have active ongoing shipments assigned.");
        }

        $name = $deliveryAgent->name;
        $deliveryAgent->delete();

        return redirect()->route('admin.delivery-agents.index')
            ->with('success', "Delivery Agent '{$name}' deleted successfully.");
    }
}
