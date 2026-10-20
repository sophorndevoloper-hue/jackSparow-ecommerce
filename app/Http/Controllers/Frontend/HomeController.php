<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\StorefrontProduct;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the storefront landing page.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->withCount('products')
            ->orderBy('sort_order')
            ->take(12)
            ->get();

        // 1. Featured components: Retrieve all products marked as featured in backend ($product->is_featured = true)
        // or curated in storefront showcase (section = 'featured', is_active = true),
        // excluding products explicitly deactivated in storefront showcase.
        // Orders by latest update and ID so the top / latest featured products appear first.
        $featuredProducts = Product::query()
            ->active()
            ->where(function ($query) {
                $query->where('is_featured', true)
                    ->orWhereHas('storefrontProducts', function ($sub) {
                        $sub->where('section', 'featured')->where('is_active', true);
                    });
            })
            ->whereDoesntHave('storefrontProducts', function ($sub) {
                $sub->where('section', 'featured')->where('is_active', false);
            })
            ->with(['brand', 'category', 'images', 'primaryImage', 'storefrontProducts' => function ($query) {
                $query->where('section', 'featured');
            }])
            ->orderByDesc('is_featured')
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString()
            ->fragment('featured-products');

        foreach ($featuredProducts->items() as $product) {
            $storefrontItem = $product->storefrontProducts->first();
            if ($storefrontItem) {
                if (! empty($storefrontItem->badge)) {
                    $product->showcase_badge = $storefrontItem->badge;
                }
                if (! empty($storefrontItem->custom_title)) {
                    $product->showcase_custom_title = $storefrontItem->custom_title;
                }
            }
        }

        // 2. Flash deals curated in Website Setup -> Website Products (for Carousel)
        $latestDeals = StorefrontProduct::getForSection('deals', 8);
        if ($latestDeals->isEmpty()) {
            $latestDeals = Product::query()
                ->active()
                ->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price')
                ->whereDoesntHave('storefrontProducts', function ($query) {
                    $query->where('is_active', false);
                })
                ->with(['brand', 'category', 'images', 'primaryImage'])
                ->take(8)
                ->get();
        }

        // 4. Authorized Component Brands
        $topBrands = Brand::query()
            ->active()
            ->withCount('products')
            ->orderByRaw('CASE WHEN logo IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(18)
            ->get();

        $userFavoriteIds = Favorite::getFavoriteIds();

        return view('frontend.home', compact(
            'categories',
            'featuredProducts',
            'latestDeals',
            'topBrands',
            'userFavoriteIds'
        ));
    }
}
