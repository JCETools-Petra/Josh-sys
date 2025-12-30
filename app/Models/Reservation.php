<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'guest_id',
        'room_type_id',
        'reservation_number',
        'check_in_date',
        'check_out_date',
        'nights',
        'adults',
        'children',
        'room_rate_per_night',
        'total_room_charge',
        'deposit_amount',
        'deposit_paid',
        'source',
        'ota_name',
        'ota_booking_id',
        'status',
        'status_changed_at',
        'special_requests',
        'notes',
        'cancellation_reason',
        'created_by',
        'confirmed_by',
        'cancelled_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'status_changed_at' => 'datetime',
        'room_rate_per_night' => 'decimal:2',
        'total_room_charge' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'deposit_paid' => 'decimal:2',
        'nights' => 'integer',
        'adults' => 'integer',
        'children' => 'integer',
    ];

    /**
     * Get the property that owns the reservation.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the guest that made the reservation.
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    /**
     * Get the room type for the reservation.
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the user who created the reservation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who confirmed the reservation.
     */
    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Get the user who cancelled the reservation.
     */
    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed reservations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include today's check-ins.
     */
    public function scopeTodayCheckIns($query)
    {
        return $query->where('check_in_date', today())
                    ->whereIn('status', ['confirmed', 'pending']);
    }

    /**
     * Scope a query to only include upcoming reservations.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('check_in_date', '>=', today())
                    ->whereIn('status', ['confirmed', 'pending'])
                    ->orderBy('check_in_date');
    }

    /**
     * Get all payments for this reservation (polymorphic).
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get deposit balance (how much deposit is still unpaid).
     */
    public function getDepositBalanceAttribute(): float
    {
        return max(0, $this->deposit_amount - $this->deposit_paid);
    }

    /**
     * Check if deposit is fully paid.
     */
    public function isDepositFullyPaid(): bool
    {
        return $this->deposit_paid >= $this->deposit_amount;
    }

    /**
     * Check if deposit is required but not paid.
     */
    public function hasUnpaidDeposit(): bool
    {
        return $this->deposit_amount > 0 && $this->deposit_paid < $this->deposit_amount;
    }
}