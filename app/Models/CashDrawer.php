<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashDrawer extends Model
{
    protected $fillable = [
        'property_id',
        'drawer_date',
        'shift_type',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'variance',
        'opening_notes',
        'closing_notes',
        'status',
    ];

    protected $casts = [
        'drawer_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'expected_balance' => 'decimal:2',
        'variance' => 'decimal:2',
    ];

    /**
     * Get the property that owns the cash drawer.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user who opened the drawer.
     */
    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Get the user who closed the drawer.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Get all transactions for this cash drawer.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * Get cash IN transactions.
     */
    public function cashInTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class)->where('type', 'in');
    }

    /**
     * Get cash OUT transactions.
     */
    public function cashOutTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class)->where('type', 'out');
    }

    /**
     * Calculate total cash IN.
     */
    public function getTotalCashInAttribute(): float
    {
        return $this->cashInTransactions()->sum('amount');
    }

    /**
     * Calculate total cash OUT.
     */
    public function getTotalCashOutAttribute(): float
    {
        return $this->cashOutTransactions()->sum('amount');
    }

    /**
     * Calculate expected balance.
     */
    public function calculateExpectedBalance(): float
    {
        return $this->opening_balance + $this->total_cash_in - $this->total_cash_out;
    }

    /**
     * Check if drawer is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if drawer is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Scope to get open drawer for a property.
     */
    public function scopeOpenForProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId)
                     ->where('status', 'open')
                     ->latest('opened_at');
    }

    /**
     * Scope to get drawers by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('drawer_date', [$startDate, $endDate]);
    }
}
