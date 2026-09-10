<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
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
            ->with(['brand', 'category', 'images']);

        if (! auth('backend')->check()) {
            $query->active();
        }

        $product = $query->firstOrFail();

        $relatedProducts = Product::query()
            ->active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['brand', 'category'])
            ->take(4)
            ->get();

        return view('frontend.product-detail', compact('product', 'relatedProducts'));
    }
}
