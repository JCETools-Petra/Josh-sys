<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RoomStay extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'hotel_room_id',
        'guest_id',
        'room_type_id',
        'confirmation_number',
        'source',
        'ota_name',
        'ota_booking_id',
        'check_in_date',
        'check_out_date',
        'actual_check_in',
        'actual_check_out',
        'room_rate_per_night',
        'bar_level',
        'total_room_charge',
        'tax_amount',
        'service_charge',
        'discount_amount',
        'discount_reason',
        'adults',
        'children',
        'special_requests',
        'status',
        'status_changed_at',
        'payment_status',
        'paid_amount',
        'checked_in_by',
        'checked_out_by',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
        'actual_check_in' => 'datetime',
        'actual_check_out' => 'datetime',
        'status_changed_at' => 'datetime',
        'room_rate_per_night' => 'decimal:2',
        'total_room_charge' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'adults' => 'integer',
        'children' => 'integer',
        'bar_level' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate confirmation number
        static::creating(function ($model) {
            if (!$model->confirmation_number) {
                $model->confirmation_number = 'CNF-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the property for this stay.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the hotel room for this stay.
     */
    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    /**
     * Get the guest for this stay.
     */
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the room type.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the staff who checked in the guest.
     */
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Get the staff who checked out the guest.
     */
    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /**
     * Get F&B orders for this stay.
     */
    public function fnbOrders()
    {
        return $this->hasMany(FnbOrder::class);
    }

    /**
     * Get payment transactions.
     */
    public function payments()
    {
        return $this->morphMany(PaymentTransaction::class, 'payable');
    }

    /**
     * Scope for active stays.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'checked_in');
    }

    /**
     * Scope for today's check-ins.
     */
    public function scopeCheckingInToday($query)
    {
        return $query->where('status', 'reserved')
            ->whereDate('check_in_date', today());
    }

    /**
     * Scope for today's check-outs.
     */
    public function scopeCheckingOutToday($query)
    {
        return $query->where('status', 'checked_in')
            ->whereDate('check_out_date', today());
    }

    /**
     * Calculate total amount including tax and service charge.
     */
    public function getTotalAmountAttribute()
    {
        return $this->total_room_charge + $this->tax_amount + $this->service_charge - $this->discount_amount;
    }

    /**
     * Get balance due.
     */
    public function getBalanceDueAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Calculate number of nights.
     */
    public function getNightsAttribute()
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return 0;
        }
        return $this->check_in_date->diffInDays($this->check_out_date);
    }
}
