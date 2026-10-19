<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class StorefrontMenu extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'storefront_menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'location',
        'parent_id',
        'title',
        'url',
        'route_name',
        'target',
        'icon',
        'badge',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Boot model events to flush storefront menu caches.
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
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get active child menu items ordered by sort_order.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Get all child menu items (including inactive) ordered by sort_order.
     */
    public function allChildren(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order', 'asc');
    }

    /**
     * Scope a query to only include items for a given location.
     */
    public function scopeLocation($query, string $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Scope a query to only include active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include top-level parent items.
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Resolve dynamic computed URL for this menu item.
     */
    public function getResolvedUrlAttribute(): string
    {
        if (! empty($this->route_name) && Route::has($this->route_name)) {
            return route($this->route_name);
        }

        if (! empty($this->url)) {
            if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://') || str_starts_with($this->url, '#')) {
                return $this->url;
            }

            return url($this->url);
        }

        return '#';
    }

    /**
     * In-memory memoized menu trees per request.
     *
     * @var array<string, Collection<int, self>>
     */
    protected static array $treeCache = [];

    /**
     * Retrieve menu tree for a location with eager-loaded children.
     *
     * @return Collection<int, self>
     */
    public static function getTree(string $location): Collection
    {
        if (isset(static::$treeCache[$location])) {
            return static::$treeCache[$location];
        }

        try {
            $tree = static::location($location)
                ->active()
                ->topLevel()
                ->orderBy('sort_order', 'asc')
                ->with('children')
                ->get();

            static::$treeCache[$location] = $tree;

            return $tree;
        } catch (\Throwable $e) {
            return new Collection;
        }
    }

    /**
     * Flush all cached storefront menu trees.
     */
    public static function flushCache(): void
    {
        static::$treeCache = [];

        try {
            Cache::forget('storefront_menu_tree_top');
            Cache::forget('storefront_menu_tree_main');
            Cache::forget('storefront_menu_tree_footer');
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
