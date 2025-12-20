<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_country_code',
        'address',
        'city',
        'country',
        'postal_code',
        'id_type',
        'id_number',
        'date_of_birth',
        'gender',
        'nationality',
        'guest_type',
        'company_name',
        'special_requests',
        'preferences',
        'source',
        'is_blacklisted',
        'blacklist_reason',
        'total_stays',
        'lifetime_value',
        'last_stay_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_blacklisted' => 'boolean',
        'total_stays' => 'integer',
        'lifetime_value' => 'decimal:2',
        'last_stay_at' => 'datetime',
    ];

    protected $appends = ['full_name'];

    /**
     * Get the guest's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get all room stays for this guest.
     */
    public function roomStays()
    {
        return $this->hasMany(RoomStay::class);
    }

    /**
     * Get active/current room stay.
     */
    public function currentStay()
    {
        return $this->hasOne(RoomStay::class)
            ->where('status', 'checked_in')
            ->latest();
    }

    /**
     * Get F&B orders for this guest.
     */
    public function fnbOrders()
    {
        return $this->hasMany(FnbOrder::class);
    }

    /**
     * Scope for VIP guests.
     */
    public function scopeVip($query)
    {
        return $query->where('guest_type', 'vip');
    }

    /**
     * Scope for corporate guests.
     */
    public function scopeCorporate($query)
    {
        return $query->where('guest_type', 'corporate');
    }

    /**
     * Scope for blacklisted guests.
     */
    public function scopeBlacklisted($query)
    {
        return $query->where('is_blacklisted', true);
    }

    /**
     * Update guest statistics after stay.
     */
    public function updateStatistics()
    {
        $this->total_stays = $this->roomStays()
            ->whereIn('status', ['checked_out', 'checked_in'])
            ->count();

        $this->lifetime_value = $this->roomStays()
            ->where('status', 'checked_out')
            ->sum('total_room_charge');

        $this->last_stay_at = $this->roomStays()
            ->latest('check_in_date')
            ->value('check_in_date');

        $this->save();
    }
}
