<?php

namespace App\Enums;

enum GuestIdType: string
{
    case KTP = 'ktp';
    case PASSPORT = 'passport';
    case SIM = 'sim';
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
            self::KTP => 'KTP',
            self::PASSPORT => 'Paspor',
            self::SIM => 'SIM',
            self::OTHER => 'Lainnya',
        };
    }

    /**
     * Get validation pattern for ID number
     */
    public function validationPattern(): ?string
    {
        return match($this) {
            self::KTP => '/^\d{16}$/', // KTP must be 16 digits
            self::PASSPORT => '/^[A-Z0-9]{6,9}$/', // Passport 6-9 alphanumeric
            self::SIM => '/^\d{12}$/', // SIM 12 digits
            self::OTHER => null, // No specific pattern
        };
    }

    /**
     * Validate ID number format
     */
    public function validate(string $idNumber): bool
    {
        $pattern = $this->validationPattern();

        if ($pattern === null) {
            return true; // No validation for OTHER
        }

        return preg_match($pattern, $idNumber) === 1;
    }
}
