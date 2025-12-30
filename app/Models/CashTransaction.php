<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashTransaction extends Model
{
    protected $fillable = [
        'cash_drawer_id',
        'type',
        'category',
        'amount',
        'reference_type',
        'reference_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the cash drawer that owns the transaction.
     */
    public function cashDrawer(): BelongsTo
    {
        return $this->belongsTo(CashDrawer::class);
    }

    /**
     * Get the user who created the transaction.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (RoomStay, Folio, etc).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if transaction is cash IN.
     */
    public function isCashIn(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Check if transaction is cash OUT.
     */
    public function isCashOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        $labels = [
            'opening_balance' => 'Saldo Awal',
            'deposit_payment' => 'Pembayaran Deposit',
            'deposit_refund' => 'Pengembalian Deposit',
            'room_payment' => 'Pembayaran Kamar',
            'change_given' => 'Kembalian',
            'additional_charge' => 'Biaya Tambahan',
            'refund' => 'Refund',
            'top_up' => 'Top Up dari Kasir',
            'deposit_to_cashier' => 'Setor ke Kasir',
            'adjustment' => 'Penyesuaian',
            'other' => 'Lainnya',
        ];

        return $labels[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Scope for cash IN transactions.
     */
    public function scopeCashIn($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope for cash OUT transactions.
     */
    public function scopeCashOut($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
