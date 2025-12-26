<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PropertySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'key',
        'value',
        'type',
        'category',
        'description',
    ];

    protected $casts = [
        'property_id' => 'integer',
    ];

    /**
     * Get the property that owns the setting.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get setting value with proper type casting.
     */
    public function getCastedValue(): mixed
    {
        return match($this->type) {
            'integer' => (int) $this->value,
            'decimal' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Get setting value for a specific property and key.
     * Uses caching for better performance.
     */
    public static function get(int $propertyId, string $key, mixed $default = null): mixed
    {
        $cacheKey = "property_setting:{$propertyId}:{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($propertyId, $key, $default) {
            $setting = self::where('property_id', $propertyId)
                ->where('key', $key)
                ->first();

            return $setting ? $setting->getCastedValue() : $default;
        });
    }

    /**
     * Set setting value for a specific property and key.
     */
    public static function set(int $propertyId, string $key, mixed $value, string $type = 'string', ?string $category = null): void
    {
        // Convert value to string for storage
        $stringValue = match($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value,
        };

        self::updateOrCreate(
            [
                'property_id' => $propertyId,
                'key' => $key,
            ],
            [
                'value' => $stringValue,
                'type' => $type,
                'category' => $category,
            ]
        );

        // Clear cache
        Cache::forget("property_setting:{$propertyId}:{$key}");
    }

    /**
     * Get all settings for a property as key-value array.
     */
    public static function getAllForProperty(int $propertyId): array
    {
        $cacheKey = "property_settings:{$propertyId}";

        return Cache::remember($cacheKey, 3600, function () use ($propertyId) {
            $settings = self::where('property_id', $propertyId)->get();

            $result = [];
            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->getCastedValue();
            }

            return $result;
        });
    }

    /**
     * Clear all cache for a property's settings.
     */
    public static function clearCache(int $propertyId): void
    {
        Cache::forget("property_settings:{$propertyId}");

        // Also clear individual keys
        $keys = self::where('property_id', $propertyId)->pluck('key');
        foreach ($keys as $key) {
            Cache::forget("property_setting:{$propertyId}:{$key}");
        }
    }

    /**
     * Boot method to clear cache on save/delete.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            self::clearCache($setting->property_id);
        });

        static::deleted(function ($setting) {
            self::clearCache($setting->property_id);
        });
    }
}
