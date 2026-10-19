<?php

namespace App\Models\Frontend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'website_settings_map';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * Flush settings cache.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Get all cached settings key-value map.
     *
     * @return array<string, mixed>
     */
    public static function getAllCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                return static::pluck('value', 'key')->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * Get a website setting value by key with optional fallback default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getAllCached();

        if (array_key_exists($key, $settings) && $settings[$key] !== null && $settings[$key] !== '') {
            return $settings[$key];
        }

        return $default;
    }

    /**
     * Set or update a single setting.
     */
    public static function set(string $key, mixed $value, string $group = 'general', ?string $label = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'group' => $group,
                'label' => $label ?: ucwords(str_replace('_', ' ', $key)),
            ]
        );

        static::flushCache();

        return $setting;
    }

    /**
     * Set multiple settings at once.
     *
     * @param  array<string, mixed>  $values
     */
    public static function setMany(array $values, string $group = 'general'): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    'group' => $group,
                    'label' => ucwords(str_replace('_', ' ', $key)),
                ]
            );
        }

        static::flushCache();
    }
}
