<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerLoginController extends Controller
{
    /**
     * Show the storefront customer sign-in form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::guard('frontend')->check()) {
            return redirect()->route('home');
        }

        return view('frontend.auth.login');
    }

    /**
     * Handle an incoming customer authentication request.
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

        // Authenticate specifically against the frontend_users table via frontend guard
        if (! Auth::guard('frontend')->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $authenticatedCustomer = Auth::guard('frontend')->user();
        if ($authenticatedCustomer && $authenticatedCustomer->is_blocked) {
            Auth::guard('frontend')->logout();

            throw ValidationException::withMessages([
                'email' => 'Your customer account has been suspended or blocked. Please contact customer support.',
            ]);
        }

        $guestFavorites = array_map('intval', session('favorites', []));

        $request->session()->regenerate();

        if (! empty($guestFavorites)) {
            $customerId = Auth::guard('frontend')->id();
            foreach ($guestFavorites as $favProductId) {
                Favorite::firstOrCreate([
                    'frontend_user_id' => $customerId,
                    'product_id' => $favProductId,
                ]);
            }
            $request->session()->forget('favorites');
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Log the customer out of the storefront.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('frontend')->logout();

        // Preserve backend admin session if an administrator is also logged in
        if (Auth::guard('backend')->check()) {
            $request->session()->regenerateToken();
        } else {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('home');
    }
}
