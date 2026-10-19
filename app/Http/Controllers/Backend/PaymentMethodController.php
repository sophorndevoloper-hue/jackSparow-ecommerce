<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of payment methods.
     */
    public function index(): View
    {
        $paymentMethods = PaymentMethod::query()
            ->withCount('orders')
            ->ordered()
            ->get();

        $totalMethods = $paymentMethods->count();
        $activeMethods = $paymentMethods->where('is_active', true)->count();
        $totalOrdersProcessed = $paymentMethods->sum('orders_count');

        return view('backend.payment-methods.index', compact(
            'paymentMethods',
            'totalMethods',
            'activeMethods',
            'totalOrdersProcessed'
        ));
    }

    /**
     * Store a newly created payment method in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:payment_methods,code'],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $method = PaymentMethod::create($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', "Payment method '{$method->name}' created successfully.");
    }

    /**
     * Update the specified payment method in storage.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('payment_methods', 'code')->ignore($paymentMethod->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $paymentMethod->update($validated);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', "Payment method '{$paymentMethod->name}' updated successfully.");
    }

    /**
     * Toggle the active status of a payment method.
     */
    public function toggleActive(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->toggleActive();
        $status = $paymentMethod->isActive() ? 'activated' : 'deactivated';

        return back()->with('success', "Payment method '{$paymentMethod->name}' has been {$status}.");
    }

    /**
     * Remove the specified payment method from storage.
     */
    public function destroy(PaymentMethod $paymentMethod): RedirectResponse
    {
        if ($paymentMethod->orders()->exists()) {
            $paymentMethod->update(['is_active' => false]);

            return back()->with('success', "Payment method '{$paymentMethod->name}' has linked orders, so it was deactivated instead of deleted.");
        }

        $name = $paymentMethod->name;
        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')
            ->with('success', "Payment method '{$name}' deleted successfully.");
    }
}
