<?php

namespace App\Enums;

enum RoomStayStatus: string
{
    case RESERVED = 'reserved';
    case CHECKED_IN = 'checked_in';
    case CHECKED_OUT = 'checked_out';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

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
            self::RESERVED => 'Reservasi',
            self::CHECKED_IN => 'Check-in',
            self::CHECKED_OUT => 'Check-out',
            self::CANCELLED => 'Dibatalkan',
            self::NO_SHOW => 'Tidak Datang',
        };
    }

    /**
     * Check if status is active (guest currently staying)
     */
    public function isActive(): bool
    {
        return $this === self::CHECKED_IN;
    }
}
