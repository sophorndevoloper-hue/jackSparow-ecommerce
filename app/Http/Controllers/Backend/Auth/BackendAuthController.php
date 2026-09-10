<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class BackendAuthController extends Controller
{
    /**
     * Show the admin portal login form using backend auth view.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('backend')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('backend.auth.login');
    }

    /**
     * Show the admin registration form using backend auth view.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::guard('backend')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('backend.auth.register');
    }

    /**
     * Handle an incoming registration request for an admin dashboard user.
     * Newly registered users are pending approval by default.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_approved' => false,
            'approved_at' => null,
        ]);

        return redirect()->route('admin.login')->with(
            'status',
            'Your registration was submitted successfully! Your account is pending administrator approval before you can access the dashboard.'
        );
    }

    /**
     * Authenticate admin dashboard personnel from the users table.
     * Enforces that the account has been approved by an administrator.
     *
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        // Authenticate strictly against users table via backend guard
        if (! Auth::guard('backend')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::guard('backend')->user();

        // Enforce admin approval workflow
        if (! $user->is_approved) {
            Auth::guard('backend')->logout();

            throw ValidationException::withMessages([
                'email' => 'Your account is pending administrator approval. Please wait for an administrator to approve your account before accessing the dashboard.',
            ]);
        }

        // Ensure user has administrative privileges or backend permissions
        $hasBackendAccess = $user->hasRole('admin', 'backend')
            || $user->hasRole('superadmin', 'backend')
            || $user->roles()->where('guard_name', 'backend')->exists()
            || $user->permissions()->where('guard_name', 'backend')->exists()
            || $user->can('access admin panel')
            || $user->can('view dashboard');

        if (! $hasBackendAccess) {
            Auth::guard('backend')->logout();

            throw ValidationException::withMessages([
                'email' => 'Access denied. You do not have permission to access the admin dashboard.',
            ]);
        }

        $request->session()->regenerate();

        $intended = session()->pull('url.intended', route('admin.dashboard'));
        if ($intended === url('/dashboard') || ! str_contains($intended, '/admin')) {
            $intended = route('admin.dashboard');
        }

        return redirect()->to($intended);
    }

    /**
     * Log out from the admin dashboard.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('backend')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
