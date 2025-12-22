<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hotel_rooms';

    protected $fillable = [
        'property_id',
        'room_number',
        'room_type_id',
        'capacity',
        'notes',
        'status',
        'last_cleaned_at',
        'last_cleaned_by',
        'assigned_hk_user_id',
        'floor',
        'is_smoking',
        'features',
    ];

    protected $casts = [
        'last_cleaned_at' => 'datetime',
        'is_smoking' => 'boolean',
        'features' => 'array',
    ];

    /**
     * Get the property that owns the room.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the room type.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the housekeeping staff assigned to this room.
     */
    public function assignedHousekeeper()
    {
        return $this->belongsTo(User::class, 'assigned_hk_user_id');
    }

    /**
     * Get amenities for this room.
     */
    public function amenities()
    {
        return $this->belongsToMany(Inventory::class, 'room_amenities', 'room_id', 'inventory_id')
            ->withPivot('quantity');
    }

    /**
     * Get all room stays for this room.
     */
    public function roomStays()
    {
        return $this->hasMany(RoomStay::class);
    }

    /**
     * Get current/active room stay.
     */
    public function currentStay()
    {
        return $this->hasOne(RoomStay::class)
            ->where('status', 'checked_in')
            ->latest();
    }

    /**
     * Get current guest (if occupied).
     */
    public function currentGuest()
    {
        return $this->hasOneThrough(
            Guest::class,
            RoomStay::class,
            'hotel_room_id',
            'id',
            'id',
            'guest_id'
        )->where('room_stays.status', 'checked_in');
    }

    /**
     * Scope for available rooms (vacant and clean).
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'vacant_clean');
    }

    /**
     * Scope for occupied rooms.
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope for dirty rooms.
     */
    public function scopeDirty($query)
    {
        return $query->where('status', 'vacant_dirty');
    }

    /**
     * Scope for rooms needing maintenance.
     */
    public function scopeNeedsMaintenance($query)
    {
        return $query->whereIn('status', ['maintenance', 'out_of_order']);
    }

    /**
     * Check if room is available for booking.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'vacant_clean';
    }

    /**
     * Mark room as clean.
     */
    public function markAsClean()
    {
        $this->update([
            'status' => 'vacant_clean',
            'last_cleaned_at' => now(),
        ]);
    }

    /**
     * Mark room as dirty.
     */
    public function markAsDirty()
    {
        $this->update(['status' => 'vacant_dirty']);
    }

    /**
     * Mark room as occupied.
     */
    public function markAsOccupied()
    {
        $this->update(['status' => 'occupied']);
    }

    /**
     * Get room display name.
     */
    public function getDisplayNameAttribute()
    {
        return $this->room_number . ($this->floor ? ' (Floor ' . $this->floor . ')' : '');
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'vacant_clean' => 'green',
            'vacant_dirty' => 'yellow',
            'occupied' => 'blue',
            'maintenance' => 'orange',
            'out_of_order' => 'red',
            'blocked' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get status label in Indonesian.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'vacant_clean' => 'Siap',
            'vacant_dirty' => 'Kotor',
            'occupied' => 'Terisi',
            'maintenance' => 'Perbaikan',
            'out_of_order' => 'Rusak',
            'blocked' => 'Diblokir',
            default => 'Unknown'
        };
    }
}
