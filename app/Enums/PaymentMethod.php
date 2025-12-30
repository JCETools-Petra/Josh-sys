<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case OTHER = 'other';

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match($this) {
            self::CASH => 'Tunai',
            self::CREDIT_CARD => 'Kartu Kredit',
            self::DEBIT_CARD => 'Kartu Debit',
            self::BANK_TRANSFER => 'Transfer Bank',
            self::OTHER => 'Lainnya',
        };
    }

    /**
     * Check if payment method requires card information
     */
    public function requiresCardInfo(): bool
    {
        return in_array($this, [self::CREDIT_CARD, self::DEBIT_CARD]);
    }
}
