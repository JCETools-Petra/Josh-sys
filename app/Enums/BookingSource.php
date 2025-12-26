<?php

namespace App\Enums;

enum BookingSource: string
{
    case WALK_IN = 'walk_in';
    case OTA = 'ota';
    case TA = 'ta';
    case CORPORATE = 'corporate';
    case GOVERNMENT = 'government';
    case COMPLIMENT = 'compliment';
    case HOUSE_USE = 'house_use';
    case AFFILIATE = 'affiliate';
    case ONLINE = 'online';

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
            self::WALK_IN => 'Walk-in',
            self::OTA => 'OTA (Online Travel Agent)',
            self::TA => 'Travel Agent',
            self::CORPORATE => 'Corporate',
            self::GOVERNMENT => 'Government',
            self::COMPLIMENT => 'Complimentary',
            self::HOUSE_USE => 'House Use',
            self::AFFILIATE => 'Affiliate',
            self::ONLINE => 'Online Booking',
        };
    }
}
