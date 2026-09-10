<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Models\FrontendUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class CustomerRegisterController extends Controller
{
    /**
     * Show the storefront customer registration form.
     */
    public function showRegisterForm(): View|RedirectResponse
    {
        if (Auth::guard('frontend')->check()) {
            return redirect()->route('home');
        }

        return view('frontend.auth.register');
    }

    /**
     * Handle an incoming customer registration request for frontend_users table.
     */
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:frontend_users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $frontendUser = FrontendUser::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'phone' => $request->input('phone'),
            'city' => $request->input('city'),
            'address' => $request->input('address'),
            'customer_type' => 'simple',
            'orders_count' => 0,
            'total_spent' => 0.00,
        ]);

        // Assign customer role on frontend guard
        $customerRole = Role::firstOrCreate([
            'name' => 'customer',
            'guard_name' => 'frontend',
        ]);
        $frontendUser->assignRole($customerRole);

        Auth::guard('frontend')->login($frontendUser);

        return redirect()->route('home')
            ->with('success', "Welcome to JackSparow TECH, {$frontendUser->name}! Your customer account is ready.");
    }
}
