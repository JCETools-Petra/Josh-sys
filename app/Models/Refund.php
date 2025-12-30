<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Refund extends Model
{
    protected $fillable = [
        'property_id',
        'refund_number',
        'room_stay_id',
        'original_payment_id',
        'amount',
        'refund_method',
        'status',
        'reason',
        'notes',
        'bank_name',
        'account_number',
        'account_holder_name',
        'reference_number',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $refund->refund_number = 'RFD-' . strtoupper(Str::random(8));
            }
        });
    }

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function roomStay(): BelongsTo
    {
        return $this->belongsTo(RoomStay::class);
    }

    public function originalPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'original_payment_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    // Helper Attributes
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processed' => 'Processed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'processed' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function getRefundMethodLabelAttribute(): string
    {
        return match($this->refund_method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->refund_method)),
        };
    }

    /**
     * Mark refund as processed.
     */
    public function markAsProcessed(?int $userId = null): void
    {
        $this->update([
            'status' => 'processed',
            'processed_by' => $userId ?? auth()->id(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Cancel the refund.
     */
    public function cancel(?string $reason = null): void
    {
        $updateData = ['status' => 'cancelled'];

        if ($reason) {
            $updateData['notes'] = ($this->notes ? $this->notes . "\n" : '') . "Cancelled: " . $reason;
        }

        $this->update($updateData);
    }
}
