<?php

namespace App\Traits;

use App\Models\PropertySetting;

trait HasPropertySettings
{
    /**
     * Get setting value for the current property.
     */
    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $property = $this->getPropertyForSettings();

        if (!$property) {
            return $default;
        }

        return PropertySetting::get($property->id, $key, $default);
    }

    /**
     * Get property instance for settings lookup.
     * Override this method if needed.
     */
    protected function getPropertyForSettings()
    {
        if (property_exists($this, 'property') && $this->property) {
            return $this->property;
        }

        if (auth()->check() && auth()->user()->property) {
            return auth()->user()->property;
        }

        return null;
    }

    /**
     * Get financial settings.
     */
    protected function getFinancialSettings(): array
    {
        return [
            'tax_rate' => $this->getSetting('tax_rate', 0.10),
            'service_charge_rate' => $this->getSetting('service_charge_rate', 0.05),
            'breakfast_rate' => $this->getSetting('breakfast_rate', 50000),
            'payment_tolerance' => $this->getSetting('payment_tolerance', 100),
        ];
    }

    /**
     * Calculate tax amount.
     */
    protected function calculateTax(float $amount): float
    {
        $taxRate = $this->getSetting('tax_rate', 0.10);
        return round($amount * $taxRate, 2);
    }

    /**
     * Calculate service charge.
     */
    protected function calculateServiceCharge(float $amount): float
    {
        $serviceRate = $this->getSetting('service_charge_rate', 0.05);
        return round($amount * $serviceRate, 2);
    }

    /**
     * Get breakfast rate.
     */
    protected function getBreakfastRate(): float
    {
        return $this->getSetting('breakfast_rate', 50000);
    }

    /**
     * Get payment tolerance for validation.
     */
    protected function getPaymentTolerance(): float
    {
        return $this->getSetting('payment_tolerance', 100);
    }
}
