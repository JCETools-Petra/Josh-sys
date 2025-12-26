<?php

namespace App\Enums;

enum RoomStatus: string
{
    case VACANT_CLEAN = 'vacant_clean';
    case VACANT_DIRTY = 'vacant_dirty';
    case OCCUPIED = 'occupied';
    case OUT_OF_ORDER = 'out_of_order';
    case OUT_OF_SERVICE = 'out_of_service';

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
            self::VACANT_CLEAN => 'Kosong Bersih',
            self::VACANT_DIRTY => 'Kosong Kotor',
            self::OCCUPIED => 'Terisi',
            self::OUT_OF_ORDER => 'Rusak',
            self::OUT_OF_SERVICE => 'Tidak Beroperasi',
        };
    }

    /**
     * Check if room is available for booking
     */
    public function isAvailable(): bool
    {
        return $this === self::VACANT_CLEAN;
    }
}
