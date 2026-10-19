<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\ProfileUpdateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerProfileController extends Controller
{
    /**
     * Display the customer profile edit screen.
     */
    public function edit(): View
    {
        $user = Auth::guard('frontend')->user();

        return view('frontend.profile', compact('user'));
    }

    /**
     * Update customer profile details (name, email, phone, shipping address, etc.).
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = Auth::guard('frontend')->user();

        $user->update($request->validated());

        return back()->with('success', 'Your profile details have been updated successfully.');
    }

    /**
     * Update customer security credentials / password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::guard('frontend')->user();

        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'string'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The provided current password does not match your account password.'], 'passwordUpdate');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Your password has been changed successfully.');
    }
}
