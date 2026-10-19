<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecentlyViewedController extends Controller
{
    /**
     * Display customer's recently viewed hardware products.
     */
    public function index(Request $request): View
    {
        $recentIds = array_map('intval', session('recently_viewed', []));

        if (empty($recentIds)) {
            $products = collect();
        } else {
            $fetched = Product::query()
                ->active()
                ->whereIn('id', $recentIds)
                ->with(['brand', 'category', 'images', 'primaryImage'])
                ->get()
                ->keyBy('id');

            // Preserve browsing chronology (most recent first)
            $products = collect($recentIds)
                ->map(fn ($id) => $fetched->get($id))
                ->filter()
                ->values();
        }

        return view('frontend.recently-viewed', compact('products'));
    }

    /**
     * Clear the customer's recently viewed history.
     */
    public function clear(Request $request): RedirectResponse
    {
        session()->forget('recently_viewed');

        return redirect()->route('recently-viewed.index')->with('success', 'Recently viewed history has been cleared.');
    }
}
