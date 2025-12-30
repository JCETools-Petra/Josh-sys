<?php

namespace App\Services;

use App\Models\CashDrawer;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class CashManagementService
{
    /**
     * Open a new cash drawer.
     */
    public function openDrawer(
        int $propertyId,
        float $openingBalance,
        string $shiftType = 'full_day',
        ?string $notes = null
    ): CashDrawer {
        DB::beginTransaction();
        try {
            // Check if there's already an open drawer for this property
            $existingDrawer = CashDrawer::openForProperty($propertyId)->first();

            if ($existingDrawer) {
                throw new Exception('Sudah ada cash drawer yang terbuka untuk properti ini. Tutup drawer terlebih dahulu.');
            }

            // Create new cash drawer
            $drawer = CashDrawer::create([
                'property_id' => $propertyId,
                'drawer_date' => now()->toDateString(),
                'shift_type' => $shiftType,
                'opened_by' => auth()->id(),
                'opened_at' => now(),
                'opening_balance' => $openingBalance,
                'opening_notes' => $notes,
                'status' => 'open',
            ]);

            // Record opening balance transaction
            $this->recordTransaction(
                $drawer->id,
                'in',
                'opening_balance',
                $openingBalance,
                'Saldo awal drawer',
                null,
                null
            );

            DB::commit();
            return $drawer;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Close a cash drawer.
     */
    public function closeDrawer(
        int $drawerId,
        float $actualClosingBalance,
        ?string $notes = null
    ): CashDrawer {
        DB::beginTransaction();
        try {
            $drawer = CashDrawer::findOrFail($drawerId);

            if ($drawer->isClosed()) {
                throw new Exception('Cash drawer sudah ditutup.');
            }

            // Calculate expected balance
            $expectedBalance = $drawer->calculateExpectedBalance();
            $variance = $actualClosingBalance - $expectedBalance;

            // Update drawer
            $drawer->update([
                'closed_by' => auth()->id(),
                'closed_at' => now(),
                'closing_balance' => $actualClosingBalance,
                'expected_balance' => $expectedBalance,
                'variance' => $variance,
                'closing_notes' => $notes,
                'status' => 'closed',
            ]);

            // If there's a variance, record adjustment transaction
            if ($variance != 0) {
                $this->recordTransaction(
                    $drawer->id,
                    $variance > 0 ? 'in' : 'out',
                    'adjustment',
                    abs($variance),
                    $variance > 0
                        ? 'Selisih lebih (cash lebih dari expected)'
                        : 'Selisih kurang (cash kurang dari expected)',
                    null,
                    null
                );
            }

            DB::commit();
            return $drawer->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record a cash transaction.
     */
    public function recordTransaction(
        int $drawerId,
        string $type,
        string $category,
        float $amount,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): CashTransaction {
        return CashTransaction::create([
            'cash_drawer_id' => $drawerId,
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Record deposit payment (cash IN).
     */
    public function recordDepositPayment(
        int $drawerId,
        float $amount,
        int $roomStayId,
        ?string $description = null
    ): CashTransaction {
        return $this->recordTransaction(
            $drawerId,
            'in',
            'deposit_payment',
            $amount,
            $description ?? 'Pembayaran deposit',
            'App\\Models\\RoomStay',
            $roomStayId
        );
    }

    /**
     * Record deposit refund (cash OUT).
     */
    public function recordDepositRefund(
        int $drawerId,
        float $amount,
        int $roomStayId,
        ?string $description = null
    ): CashTransaction {
        return $this->recordTransaction(
            $drawerId,
            'out',
            'deposit_refund',
            $amount,
            $description ?? 'Pengembalian deposit',
            'App\\Models\\RoomStay',
            $roomStayId
        );
    }

    /**
     * Record change given (cash OUT).
     */
    public function recordChangeGiven(
        int $drawerId,
        float $amount,
        int $roomStayId,
        ?string $description = null
    ): CashTransaction {
        return $this->recordTransaction(
            $drawerId,
            'out',
            'change_given',
            $amount,
            $description ?? 'Kembalian untuk tamu',
            'App\\Models\\RoomStay',
            $roomStayId
        );
    }

    /**
     * Record room payment (cash IN).
     */
    public function recordRoomPayment(
        int $drawerId,
        float $amount,
        int $roomStayId,
        ?string $description = null
    ): CashTransaction {
        return $this->recordTransaction(
            $drawerId,
            'in',
            'room_payment',
            $amount,
            $description ?? 'Pembayaran kamar',
            'App\\Models\\RoomStay',
            $roomStayId
        );
    }

    /**
     * Record top-up from cashier (cash IN).
     */
    public function recordTopUp(
        int $drawerId,
        float $amount,
        ?string $description = null
    ): CashTransaction {
        return $this->recordTransaction(
            $drawerId,
            'in',
            'top_up',
            $amount,
            $description ?? 'Top up dari kasir',
            null,
            null
        );
    }

    /**
     * Record deposit to cashier (cash OUT).
     */
    public function recordDepositToCashier(
        int $drawerId,
        float $amount,
        ?string $description = null
    ): CashTransaction {
        return $this->recordTransaction(
            $drawerId,
            'out',
            'deposit_to_cashier',
            $amount,
            $description ?? 'Setor ke kasir',
            null,
            null
        );
    }

    /**
     * Get active drawer for a property.
     */
    public function getActiveDrawer(int $propertyId): ?CashDrawer
    {
        return CashDrawer::openForProperty($propertyId)->first();
    }

    /**
     * Get drawer by ID with transactions.
     */
    public function getDrawerWithTransactions(int $drawerId): CashDrawer
    {
        return CashDrawer::with(['transactions', 'openedBy', 'closedBy', 'property'])
            ->findOrFail($drawerId);
    }

    /**
     * Get drawers for a date range.
     */
    public function getDrawersByDateRange(
        int $propertyId,
        Carbon $startDate,
        Carbon $endDate
    ) {
        return CashDrawer::where('property_id', $propertyId)
            ->dateRange($startDate, $endDate)
            ->with(['openedBy', 'closedBy'])
            ->latest('drawer_date')
            ->get();
    }

    /**
     * Get cash summary for a drawer.
     */
    public function getDrawerSummary(int $drawerId): array
    {
        $drawer = $this->getDrawerWithTransactions($drawerId);

        $summary = [
            'opening_balance' => $drawer->opening_balance,
            'total_cash_in' => 0,
            'total_cash_out' => 0,
            'expected_balance' => 0,
            'closing_balance' => $drawer->closing_balance,
            'variance' => $drawer->variance,
            'transactions_by_category' => [],
        ];

        // Group transactions by category
        foreach ($drawer->transactions as $transaction) {
            if (!isset($summary['transactions_by_category'][$transaction->category])) {
                $summary['transactions_by_category'][$transaction->category] = [
                    'label' => $transaction->category_label,
                    'in' => 0,
                    'out' => 0,
                    'count' => 0,
                ];
            }

            if ($transaction->type === 'in') {
                $summary['total_cash_in'] += $transaction->amount;
                $summary['transactions_by_category'][$transaction->category]['in'] += $transaction->amount;
            } else {
                $summary['total_cash_out'] += $transaction->amount;
                $summary['transactions_by_category'][$transaction->category]['out'] += $transaction->amount;
            }

            $summary['transactions_by_category'][$transaction->category]['count']++;
        }

        $summary['expected_balance'] = $summary['opening_balance'] + $summary['total_cash_in'] - $summary['total_cash_out'];

        return $summary;
    }
}
