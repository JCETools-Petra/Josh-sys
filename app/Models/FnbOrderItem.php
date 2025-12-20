<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FnbOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fnb_order_id',
        'fnb_menu_item_id',
        'quantity',
        'unit_price',
        'special_instructions',
        'status',
        'status_changed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'status_changed_at' => 'datetime',
    ];

    /**
     * Get the order that owns this item.
     */
    public function order()
    {
        return $this->belongsTo(FnbOrder::class, 'fnb_order_id');
    }

    /**
     * Get the menu item.
     */
    public function menuItem()
    {
        return $this->belongsTo(FnbMenuItem::class, 'fnb_menu_item_id');
    }

    /**
     * Calculate subtotal.
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Mark item as ready.
     */
    public function markAsReady()
    {
        $this->update([
            'status' => 'ready',
            'status_changed_at' => now(),
        ]);
    }

    /**
     * Mark item as served.
     */
    public function markAsServed()
    {
        $this->update([
            'status' => 'served',
            'status_changed_at' => now(),
        ]);
    }
}
