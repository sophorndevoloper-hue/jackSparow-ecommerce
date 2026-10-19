<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    /**
     * Display a specific computer part detail page.
     */
    public function show(string $slug): View
    {
        $query = Product::query()
            ->where('slug', $slug)
            ->with(['brand', 'category', 'images', 'primaryImage']);

        if (! auth('backend')->check()) {
            $query->active();
        }

        $product = $query->firstOrFail();

        // Track recently viewed products in session
        $recentlyViewedSession = array_map('intval', session('recently_viewed', []));
        $otherRecentIds = array_values(array_diff($recentlyViewedSession, [$product->id]));

        // Fetch up to 4 other recently viewed items for preview
        $recentlyViewed = collect();
        if (! empty($otherRecentIds)) {
            $previewIds = array_slice($otherRecentIds, 0, 4);
            $fetched = Product::query()
                ->active()
                ->whereIn('id', $previewIds)
                ->with(['brand', 'category', 'images', 'primaryImage'])
                ->get()
                ->keyBy('id');

            $recentlyViewed = collect($previewIds)
                ->map(fn ($id) => $fetched->get($id))
                ->filter()
                ->values();
        }

        // Prepend current product and store up to 12 in session
        $updatedRecent = array_slice(array_merge([$product->id], $otherRecentIds), 0, 12);
        session(['recently_viewed' => $updatedRecent]);

        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['brand', 'category', 'images', 'primaryImage'])
            ->take(4)
            ->get();

        $isFavorited = Favorite::isFavorited($product->id);
        $userFavoriteIds = Favorite::getFavoriteIds();

        return view('frontend.product-detail', compact('product', 'relatedProducts', 'recentlyViewed', 'isFavorited', 'userFavoriteIds'));
    }
}
