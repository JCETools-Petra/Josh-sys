<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FnbMenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'name',
        'code',
        'description',
        'image_url',
        'category',
        'subcategory',
        'price',
        'cost',
        'tax_rate',
        'service_charge_rate',
        'is_available',
        'available_from',
        'available_until',
        'is_vegetarian',
        'is_halal',
        'is_spicy',
        'allergens',
        'prep_time_minutes',
        'total_sold',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'is_available' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_halal' => 'boolean',
        'is_spicy' => 'boolean',
        'prep_time_minutes' => 'integer',
        'total_sold' => 'integer',
    ];

    /**
     * Get the property that owns the menu item.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get order items for this menu item.
     */
    public function orderItems()
    {
        return $this->hasMany(FnbOrderItem::class);
    }

    /**
     * Scope for available items.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope by category.
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for breakfast items.
     */
    public function scopeBreakfast($query)
    {
        return $query->where('category', 'breakfast');
    }

    /**
     * Scope for lunch items.
     */
    public function scopeLunch($query)
    {
        return $query->where('category', 'lunch');
    }

    /**
     * Scope for dinner items.
     */
    public function scopeDinner($query)
    {
        return $query->where('category', 'dinner');
    }

    /**
     * Calculate price with tax and service charge.
     */
    public function getPriceWithTaxAttribute()
    {
        $taxAmount = $this->price * ($this->tax_rate / 100);
        $serviceAmount = $this->price * ($this->service_charge_rate / 100);
        return $this->price + $taxAmount + $serviceAmount;
    }

    /**
     * Calculate profit margin.
     */
    public function getProfitMarginAttribute()
    {
        if (!$this->cost || $this->cost == 0) {
            return 0;
        }
        return (($this->price - $this->cost) / $this->price) * 100;
    }

    /**
     * Check if item is currently available based on time.
     */
    public function isCurrentlyAvailable(): bool
    {
        if (!$this->is_available) {
            return false;
        }

        if (!$this->available_from || !$this->available_until) {
            return true;
        }

        $now = now()->format('H:i:s');
        return $now >= $this->available_from && $now <= $this->available_until;
    }
}
