<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

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
            self::PENDING => 'Menunggu',
            self::COMPLETED => 'Selesai',
            self::FAILED => 'Gagal',
            self::REFUNDED => 'Dikembalikan',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
