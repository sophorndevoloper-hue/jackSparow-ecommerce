<?php

namespace App\Models\Frontend;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'frontend_user_id',
    'product_id',
])]
class Favorite extends Model
{
    use HasFactory;

    /**
     * The customer who favorited this product.
     *
     * @return BelongsTo<FrontendUser, $this>
     */
    public function frontendUser(): BelongsTo
    {
        return $this->belongsTo(FrontendUser::class, 'frontend_user_id');
    }

    /**
     * The product that was favorited.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get all favorited product IDs for the current user (database or session).
     *
     * @return list<int>
     */
    public static function getFavoriteIds(): array
    {
        if (auth('frontend')->check()) {
            return static::where('frontend_user_id', auth('frontend')->id())
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        }

        return array_values(array_map('intval', array_unique(session('favorites', []))));
    }

    /**
     * Check whether a specific product is favorited by the current user.
     */
    public static function isFavorited(int $productId): bool
    {
        return in_array($productId, static::getFavoriteIds(), true);
    }

    /**
     * Get total count of favorites for the current user.
     */
    public static function getCount(): int
    {
        return count(static::getFavoriteIds());
    }
}
