<?php

namespace App\Models\Frontend;

use App\Models\Product\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StorefrontProduct extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'storefront_products';

    /**
     * Supported showcase sections.
     *
     * @var array<string, string>
     */
    public const SECTIONS = [
        'featured' => 'Featured Components',
        'deals' => 'Flash Deals & Clearance',
        'bestseller' => 'Best Sellers',
    ];

    /**
     * In-memory cache for section queries.
     *
     * @var array<string, Collection>
     */
    protected static array $sectionCache = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'section',
        'showcaseable_type',
        'showcaseable_id',
        'badge',
        'custom_title',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'product_id' => 'integer',
        'showcaseable_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot model events to flush showcase caches.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::flushCache();
        });

        static::deleted(function () {
            static::flushCache();
        });
    }

    /**
     * Flush memory cache.
     */
    public static function flushCache(): void
    {
        static::$sectionCache = [];
    }

    /**
     * Relationship to the inventory Product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Polymorphic relationship to the parent showcase section entity.
     */
    public function showcaseable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to active items.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }

    /**
     * Scope to a specific showcase section.
     */
    public function scopeSection(Builder $query, string $section): Builder
    {
        return $query->where($this->qualifyColumn('section'), $section);
    }

    /**
     * Scope ordered by sort order and creation.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->qualifyColumn('sort_order'), 'asc')->orderBy($this->qualifyColumn('id'), 'asc');
    }

    /**
     * Retrieve curated Product models for a storefront section.
     *
     * @return Collection<int, Product>
     */
    public static function getForSection(string $section, int $limit = 8): Collection
    {
        $cacheKey = "{$section}_{$limit}";

        if (isset(static::$sectionCache[$cacheKey])) {
            return static::$sectionCache[$cacheKey];
        }

        $query = static::query()
            ->active()
            ->section($section)
            ->ordered()
            ->with(['product.brand', 'product.category', 'product.images', 'product.primaryImage', 'showcaseable']);

        $items = $query->take($limit)->get();

        // Extract products with storefront showcase metadata attached
        $products = new Collection;

        foreach ($items as $item) {
            if ($item->product && $item->product->is_active) {
                $product = $item->product;
                $product->showcase_badge = $item->badge;
                $product->showcase_custom_title = $item->custom_title;
                $product->showcase_sort_order = $item->sort_order;
                $product->storefront_product_id = $item->id;
                $product->showcaseable = $item->showcaseable;
                $products->push($product);
            }
        }

        static::$sectionCache[$cacheKey] = $products;

        return $products;
    }
}
