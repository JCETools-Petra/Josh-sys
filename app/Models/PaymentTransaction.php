<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'payable_id',
        'payable_type',
        'transaction_number',
        'amount',
        'payment_method',
        'card_type',
        'card_last_four',
        'bank_name',
        'account_name',
        'reference_number',
        'status',
        'completed_at',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate transaction number
        static::creating(function ($model) {
            if (!$model->transaction_number) {
                $model->transaction_number = 'TRX-' . strtoupper(Str::random(10)) . '-' . now()->format('YmdHis');
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
     * Get the payable model (RoomStay or FnbOrder).
     */
    public function payable()
    {
        return $this->morphTo();
    }

    /**
     * Get the staff who processed the payment.
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope for completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for today's payments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
