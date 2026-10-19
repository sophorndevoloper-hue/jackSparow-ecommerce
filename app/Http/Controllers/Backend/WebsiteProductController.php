<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BestSeller;
use App\Models\FeaturedComponent;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\StorefrontProduct;
use App\Models\UserActivity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteProductController extends Controller
{
    /**
     * Display a listing of curated website showcase products by section.
     */
    public function index(Request $request): View
    {
        $section = $request->query('section', 'featured');
        if (! array_key_exists($section, StorefrontProduct::SECTIONS)) {
            $section = 'featured';
        }

        // Curated showcase items for the selected section
        $showcaseProducts = StorefrontProduct::query()
            ->section($section)
            ->with(['product.category', 'product.brand', 'product.images', 'product.primaryImage', 'showcaseable'])
            ->ordered()
            ->get();

        // Catalog products available to add to website
        $catalogProducts = Product::query()
            ->where('is_active', true)
            ->with(['category', 'brand', 'images', 'primaryImage'])
            ->orderBy('name')
            ->get();

        // Counts for tab indicators
        $counts = [];
        foreach (StorefrontProduct::SECTIONS as $secKey => $secLabel) {
            $counts[$secKey] = StorefrontProduct::section($secKey)->count();
        }

        $sections = StorefrontProduct::SECTIONS;

        $catalogHardwareData = $catalogProducts->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => (float) $p->price,
            'formatted_price' => number_format((float) $p->price, 2),
            'stock_quantity' => (int) $p->stock_quantity,
            'category' => $p->category?->name,
            'brand' => $p->brand?->name,
            'primary_image_url' => $p->primary_image_url,
        ]);

        return view('backend.website-products.index', compact(
            'showcaseProducts',
            'catalogProducts',
            'catalogHardwareData',
            'section',
            'sections',
            'counts'
        ));
    }

    /**
     * Add a product to a website showcase section.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id'],
            'section' => ['required', 'string', 'in:'.implode(',', array_keys(StorefrontProduct::SECTIONS))],
            'badge' => ['nullable', 'string', 'max:50'],
            'custom_title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ids = collect($request->input('product_ids', []))
            ->filter()
            ->merge([$request->input('product_id')])
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->withErrors(['product_id' => 'Please select at least one hardware product from inventory.']);
        }

        $section = $validated['section'];
        $sectionLabel = StorefrontProduct::SECTIONS[$section] ?? $section;
        $lastItem = null;

        $showcaseableType = null;
        $showcaseableId = null;

        if ($section === 'featured') {
            $parent = FeaturedComponent::firstOrCreate(['title' => 'Featured High-Performance Components'], ['is_active' => true, 'sort_order' => 1]);
            $showcaseableType = FeaturedComponent::class;
            $showcaseableId = $parent->id;
        } elseif ($section === 'deals') {
            $parent = FlashDeal::firstOrCreate(['title' => 'Flash Deals & Hardware Clearance'], ['is_active' => true, 'sort_order' => 1]);
            $showcaseableType = FlashDeal::class;
            $showcaseableId = $parent->id;
        } elseif ($section === 'bestseller') {
            $parent = BestSeller::firstOrCreate(['title' => 'Community Best Sellers'], ['is_active' => true, 'sort_order' => 1]);
            $showcaseableType = BestSeller::class;
            $showcaseableId = $parent->id;
        }

        foreach ($ids as $idx => $pid) {
            $item = StorefrontProduct::updateOrCreate(
                [
                    'product_id' => $pid,
                    'section' => $section,
                ],
                [
                    'showcaseable_type' => $showcaseableType,
                    'showcaseable_id' => $showcaseableId,
                    'badge' => $validated['badge'] ?? null,
                    'custom_title' => $ids->count() === 1 ? ($validated['custom_title'] ?? null) : null,
                    'sort_order' => ($validated['sort_order'] ?? 0) + $idx,
                    'is_active' => $request->boolean('is_active', true),
                ]
            );

            $lastItem = $item;
            $productName = $item->product?->name ?? "Product #{$item->product_id}";

            UserActivity::log(
                'CREATE',
                'Website Products',
                "Added \"{$productName}\" to Storefront Section \"{$sectionLabel}\"",
                $item,
                ['new_data' => $item->toArray()]
            );
        }

        StorefrontProduct::flushCache();

        $count = $ids->count();
        $msg = $count === 1
            ? "Product added to {$sectionLabel} showcase successfully."
            : "{$count} products added to {$sectionLabel} showcase successfully.";

        return redirect()
            ->route('admin.website-products.index', ['section' => $section])
            ->with('success', $msg);
    }

    /**
     * Update showcase details (badge, custom title, sort order, active).
     */
    public function update(Request $request, StorefrontProduct $storefrontProduct): RedirectResponse
    {
        $validated = $request->validate([
            'badge' => ['nullable', 'string', 'max:50'],
            'custom_title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = $storefrontProduct->toArray();

        $storefrontProduct->update([
            'badge' => $validated['badge'] ?? null,
            'custom_title' => $validated['custom_title'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $storefrontProduct->sort_order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        StorefrontProduct::flushCache();

        $productName = $storefrontProduct->product?->name ?? "Product #{$storefrontProduct->product_id}";
        $sectionLabel = StorefrontProduct::SECTIONS[$storefrontProduct->section] ?? $storefrontProduct->section;

        UserActivity::log(
            'UPDATE',
            'Website Products',
            "Updated showcase settings for \"{$productName}\" in \"{$sectionLabel}\"",
            $storefrontProduct,
            ['old_data' => $oldData, 'new_data' => $storefrontProduct->toArray()]
        );

        return redirect()
            ->route('admin.website-products.index', ['section' => $storefrontProduct->section])
            ->with('success', 'Showcase product updated successfully.');
    }

    /**
     * Remove a product from the website showcase section.
     */
    public function destroy(StorefrontProduct $storefrontProduct): RedirectResponse
    {
        $section = $storefrontProduct->section;
        $productName = $storefrontProduct->product?->name ?? "Product #{$storefrontProduct->product_id}";
        $sectionLabel = StorefrontProduct::SECTIONS[$section] ?? $section;
        $oldData = $storefrontProduct->toArray();

        $storefrontProduct->delete();
        StorefrontProduct::flushCache();

        UserActivity::log(
            'DELETE',
            'Website Products',
            "Removed \"{$productName}\" from Storefront Section \"{$sectionLabel}\"",
            $storefrontProduct,
            ['old_data' => $oldData]
        );

        return redirect()
            ->route('admin.website-products.index', ['section' => $section])
            ->with('success', "Product removed from {$sectionLabel} showcase.");
    }

    /**
     * Toggle the active visibility status of a showcase product.
     */
    public function toggleActive(StorefrontProduct $storefrontProduct): JsonResponse|RedirectResponse
    {
        $oldState = $storefrontProduct->is_active;
        $storefrontProduct->update([
            'is_active' => ! $oldState,
        ]);

        StorefrontProduct::flushCache();

        $statusText = $storefrontProduct->is_active ? 'displayed on' : 'hidden from';
        $productName = $storefrontProduct->product?->name ?? "Product #{$storefrontProduct->product_id}";

        UserActivity::log(
            'TOGGLE_STATUS',
            'Website Products',
            "Product \"{$productName}\" is now {$statusText} the website",
            $storefrontProduct,
            ['is_active' => $storefrontProduct->is_active]
        );

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $storefrontProduct->is_active,
                'message' => "Product is now {$statusText} website.",
            ]);
        }

        return redirect()
            ->route('admin.website-products.index', ['section' => $storefrontProduct->section])
            ->with('success', "Product is now {$statusText} website.");
    }
}
