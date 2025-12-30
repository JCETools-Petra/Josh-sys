<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'key',
        'allowed_origins',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'key',
    ];

    /**
     * Generate a new unique API key
     */
    public static function generateKey(): string
    {
        do {
            $key = 'htk_' . Str::random(48);
        } while (self::where('key', $key)->exists());

        return $key;
    }

    /**
     * Check if the origin is allowed
     */
    public function isOriginAllowed(?string $origin): bool
    {
        if (empty($this->allowed_origins)) {
            return true; // If no restrictions, allow all
        }

        $allowedOrigins = array_map('trim', explode(',', $this->allowed_origins));

        foreach ($allowedOrigins as $allowedOrigin) {
            if ($allowedOrigin === '*' || $origin === $allowedOrigin) {
                return true;
            }

            // Support wildcard subdomains: *.example.com
            if (str_starts_with($allowedOrigin, '*.')) {
                $domain = substr($allowedOrigin, 2);
                if (str_ends_with($origin, $domain)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Update the last used timestamp
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get the property that owns this API key
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
