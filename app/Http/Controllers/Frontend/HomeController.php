<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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
            ->take(8)
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->with(['brand', 'category'])
            ->take(8)
            ->get();

        $latestDeals = Product::query()
            ->active()
            ->whereNotNull('sale_price')
            ->with(['brand', 'category'])
            ->take(4)
            ->get();

        $topBrands = Brand::query()
            ->active()
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(8)
            ->get();

        return view('frontend.home', compact('categories', 'featuredProducts', 'latestDeals', 'topBrands'));
    }
}
