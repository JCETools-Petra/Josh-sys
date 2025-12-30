<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'property_id',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'two_factor_verified_at' => 'datetime',
        ];
    }

    /**
     * Generate a new 2FA code
     */
    public function generateTwoFactorCode(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'two_factor_code' => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    /**
     * Check if 2FA code is valid
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_code) {
            return false;
        }

        if ($this->two_factor_expires_at && $this->two_factor_expires_at->isPast()) {
            return false;
        }

        return $this->two_factor_code === $code;
    }

    /**
     * Mark 2FA as verified for this session
     */
    public function markTwoFactorVerified(): void
    {
        $this->update([
            'two_factor_verified_at' => now(),
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);
    }

    /**
     * Check if 2FA verification is required
     */
    public function needsTwoFactorVerification(): bool
    {
        if (!$this->two_factor_enabled) {
            return false;
        }

        // If verified less than 30 days ago, no need to verify again
        if ($this->two_factor_verified_at && $this->two_factor_verified_at->isAfter(now()->subDays(30))) {
            return false;
        }

        return true;
    }

    /**
     * TAMBAHAN: Definisikan relasi "belongsTo" ke model Property.
     * Ini memungkinkan kita untuk mengambil data properti dari user.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function isHousekeeper()
    {
        return $this->role === 'hk';
    }

    /**
     * Get the rooms assigned to this housekeeper.
     */
    public function assignedRooms()
    {
        return $this->hasMany(HotelRoom::class, 'assigned_hk_user_id');
    }
}
