<?php

namespace App\Models;

use App\Events\PaymentCreated;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'property_id',
        'payment_number',
        'payable_id',
        'payable_type',
        'payment_method',
        'amount',
        'currency',
        'card_number_last4',
        'card_holder_name',
        'card_type',
        'bank_name',
        'account_number',
        'reference_number',
        'notes',
        'status',
        'payment_date',
        'processed_by',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                do {
                    $payment->payment_number = 'PAY-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8));
                } while (self::where('payment_number', $payment->payment_number)->exists());
            }
        });

        // Dispatch PaymentCreated event after payment is created
        static::created(function ($payment) {
            event(new PaymentCreated($payment));
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
