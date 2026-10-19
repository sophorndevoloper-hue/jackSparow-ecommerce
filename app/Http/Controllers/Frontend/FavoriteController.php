<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display the customer's saved favorite hardware products.
     */
    public function index(Request $request): View
    {
        $favoriteIds = Favorite::getFavoriteIds();

        $products = Product::query()
            ->active()
            ->whereIn('id', $favoriteIds)
            ->with(['brand', 'category', 'images', 'primaryImage'])
            ->latest()
            ->paginate(12);

        return view('frontend.favorites', compact('products'));
    }

    /**
     * Toggle favorite status of a product (AJAX or form submission).
     */
    public function toggle(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $isFavorited = false;
        $message = '';

        if (auth('frontend')->check()) {
            $userId = auth('frontend')->id();
            $existing = Favorite::query()
                ->where('frontend_user_id', $userId)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $isFavorited = false;
                $message = 'Removed from your favorites.';
            } else {
                Favorite::create([
                    'frontend_user_id' => $userId,
                    'product_id' => $product->id,
                ]);
                $isFavorited = true;
                $message = 'Added to your favorites!';
            }
        } else {
            $favorites = array_map('intval', session('favorites', []));

            if (in_array($product->id, $favorites, true)) {
                $favorites = array_values(array_diff($favorites, [$product->id]));
                $isFavorited = false;
                $message = 'Removed from your favorites.';
            } else {
                $favorites[] = $product->id;
                $isFavorited = true;
                $message = 'Added to your favorites!';
            }

            session(['favorites' => array_values(array_unique($favorites))]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_favorited' => $isFavorited,
                'favorite_count' => Favorite::getCount(),
                'message' => $message,
                'product_name' => $product->name,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Clear all saved favorites.
     */
    public function clear(Request $request): RedirectResponse
    {
        if (auth('frontend')->check()) {
            Favorite::query()
                ->where('frontend_user_id', auth('frontend')->id())
                ->delete();
        } else {
            session()->forget('favorites');
        }

        return redirect()->route('favorites.index')->with('success', 'All saved favorites have been cleared.');
    }
}
