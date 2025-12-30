<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RoomStay extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'property_id',
        'reservation_id',
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
        'fnb_charges',
        'adults',
        'children',
        'with_breakfast',
        'breakfast_rate',
        'total_breakfast_charge',
        'special_requests',
        'status',
        'status_changed_at',
        'payment_status',
        'paid_amount',
        'checked_in_by',
        'checked_out_by',
        'notes',
        'folio_locked_at',
    ];

    protected $casts = [
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
        'actual_check_in' => 'datetime',
        'actual_check_out' => 'datetime',
        'status_changed_at' => 'datetime',
        'folio_locked_at' => 'datetime',
        'room_rate_per_night' => 'decimal:2',
        'total_room_charge' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fnb_charges' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'adults' => 'integer',
        'children' => 'integer',
        'with_breakfast' => 'boolean',
        'breakfast_rate' => 'decimal:2',
        'total_breakfast_charge' => 'decimal:2',
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
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Get refund transactions.
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
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
     * Menampilkan tamu yang melakukan check-in hari ini (berdasarkan actual_check_in).
     */
    public function scopeCheckingInToday($query)
    {
        return $query->whereNotNull('actual_check_in')
            ->whereDate('actual_check_in', today());
    }

    /**
     * 🔧 BUG FIX: Scope for pending check-in today.
     * Menampilkan reservasi yang SCHEDULED untuk check-in hari ini (status reserved, belum check-in).
     * Ini untuk menampilkan daftar reservasi yang perlu diproses check-in.
     */
    public function scopePendingCheckInToday($query)
    {
        return $query->where('status', 'reserved')
            ->whereDate('check_in_date', today())
            ->whereNull('actual_check_in');
    }

    /**
     * Scope for today's check-outs.
     * Menampilkan tamu yang melakukan check-out hari ini (berdasarkan actual_check_out).
     */
    public function scopeCheckingOutToday($query)
    {
        return $query->whereNotNull('actual_check_out')
            ->whereDate('actual_check_out', today());
    }

    /**
     * Scope for pending checkout today.
     * Tamu yang SCHEDULED checkout hari ini tapi BELUM checkout (masih status checked_in).
     */
    public function scopePendingCheckoutToday($query)
    {
        return $query->where('status', 'checked_in')
            ->whereDate('check_out_date', today())
            ->whereNull('actual_check_out');
    }

    /**
     * Scope for overdue checkout.
     * Tamu yang sudah LEWAT tanggal checkout tapi BELUM checkout (masih status checked_in).
     */
    public function scopeOverdueCheckout($query)
    {
        return $query->where('status', 'checked_in')
            ->where('check_out_date', '<', now())
            ->whereNull('actual_check_out');
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

    /**
     * Get the linked reservation if this room stay came from a reservation.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Record a payment for this room stay.
     */
    public function recordPayment(float $amount, string $method, string $notes = null, $user = null): Payment
    {
        $payment = Payment::create([
            'property_id' => $this->property_id,
            'payment_number' => 'PAY-' . strtoupper(Str::random(8)),
            'payable_type' => self::class,
            'payable_id' => $this->id,
            'payment_method' => $method,
            'amount' => $amount,
            'status' => 'completed',
            'payment_date' => now(),
            'notes' => $notes,
            'processed_by' => $user ? $user->id : auth()->id(),
        ]);

        // Update paid amount
        $this->increment('paid_amount', $amount);
        $this->updatePaymentStatus();

        return $payment;
    }

    /**
     * Update payment status based on paid amount.
     */
    public function updatePaymentStatus(): void
    {
        $totalAmount = $this->total_amount;
        $paidAmount = $this->paid_amount;

        if ($paidAmount <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paidAmount >= $totalAmount) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        $this->save();
    }
}
