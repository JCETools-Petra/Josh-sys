<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LostAndFound extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'lost_and_found';

    protected $fillable = [
        'property_id',
        'item_number',
        'item_name',
        'category',
        'description',
        'color',
        'brand',
        'hotel_room_id',
        'location_found',
        'date_found',
        'found_by',
        'guest_id',
        'room_stay_id',
        'status',
        'storage_location',
        'disposal_date',
        'claimed_at',
        'claimed_by_guest',
        'claimed_by_name',
        'claimed_by_phone',
        'claim_notes',
        'released_by',
        'photos',
        'notes',
    ];

    protected $casts = [
        'date_found' => 'date',
        'disposal_date' => 'date',
        'claimed_at' => 'datetime',
        'photos' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->item_number) {
                $model->item_number = 'LF-' . strtoupper(Str::random(6)) . '-' . now()->format('dmy');
            }
        });
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function foundBy()
    {
        return $this->belongsTo(User::class, 'found_by');
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function roomStay()
    {
        return $this->belongsTo(RoomStay::class);
    }

    public function claimedByGuest()
    {
        return $this->belongsTo(Guest::class, 'claimed_by_guest');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * Scope for stored items.
     */
    public function scopeStored($query)
    {
        return $query->where('status', 'stored');
    }

    /**
     * Scope for claimed items.
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    /**
     * Scope for items ready for disposal (90+ days unclaimed).
     */
    public function scopeReadyForDisposal($query)
    {
        return $query->where('status', 'stored')
                    ->where('date_found', '<=', now()->subDays(90));
    }
}
