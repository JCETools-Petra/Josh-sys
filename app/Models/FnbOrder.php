<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FnbOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'order_type',
        'guest_id',
        'room_stay_id',
        'hotel_room_id',
        'table_number',
        'number_of_guests',
        'customer_name',
        'customer_phone',
        'order_number',
        'order_time',
        'delivery_time',
        'subtotal',
        'tax_amount',
        'service_charge',
        'discount_amount',
        'discount_reason',
        'delivery_charge',
        'payment_status',
        'payment_method',
        'paid_amount',
        'paid_at',
        'status',
        'status_changed_at',
        'taken_by',
        'served_by',
        'special_instructions',
        'notes',
        'rating',
        'feedback',
    ];

    protected $casts = [
        'order_time' => 'datetime',
        'delivery_time' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'status_changed_at' => 'datetime',
        'number_of_guests' => 'integer',
        'rating' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate order number
        static::creating(function ($model) {
            if (!$model->order_number) {
                $prefix = match($model->order_type) {
                    'dine_in' => 'DI',
                    'room_service' => 'RS',
                    'takeaway' => 'TA',
                    'delivery' => 'DL',
                    default => 'OR'
                };
                $model->order_number = $prefix . '-' . strtoupper(Str::random(6)) . '-' . now()->format('dmy');
            }

            if (!$model->order_time) {
                $model->order_time = now();
            }
        });
    }

    /**
     * Get the property.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the guest.
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the room stay (if room service).
     */
    public function roomStay()
    {
        return $this->belongsTo(RoomStay::class);
    }

    /**
     * Get the hotel room (if room service).
     */
    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    /**
     * Get the staff who took the order.
     */
    public function takenByUser()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }

    /**
     * Get the staff who served the order.
     */
    public function servedByUser()
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    /**
     * Get order items.
     */
    public function items()
    {
        return $this->hasMany(FnbOrderItem::class);
    }

    /**
     * Get payment transactions.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Scope for today's orders.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('order_time', today());
    }

    /**
     * Scope for pending orders.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed', 'preparing']);
    }

    /**
     * Scope for room service orders.
     */
    public function scopeRoomService($query)
    {
        return $query->where('order_type', 'room_service');
    }

    /**
     * Scope for dine-in orders.
     */
    public function scopeDineIn($query)
    {
        return $query->where('order_type', 'dine_in');
    }

    /**
     * Calculate total amount.
     */
    public function getTotalAmountAttribute()
    {
        return $this->subtotal + $this->tax_amount + $this->service_charge + $this->delivery_charge - $this->discount_amount;
    }

    /**
     * Calculate balance due.
     */
    public function getBalanceDueAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Recalculate order totals from items.
     */
    public function recalculateTotals()
    {
        $this->subtotal = $this->items->sum('subtotal');

        // Calculate tax and service charge based on subtotal
        $this->tax_amount = $this->subtotal * 0.10; // 10% tax
        $this->service_charge = $this->subtotal * 0.05; // 5% service charge

        $this->save();
    }

    /**
     * Mark order as completed.
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'status_changed_at' => now(),
        ]);
    }
}
