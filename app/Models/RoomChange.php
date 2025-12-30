<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomChange extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'property_id',
        'room_stay_id',
        'old_room_id',
        'new_room_id',
        'change_type',
        'old_check_out_date',
        'new_check_out_date',
        'old_rate',
        'new_rate',
        'additional_charge',
        'reason',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'old_check_out_date' => 'date',
        'new_check_out_date' => 'date',
        'old_rate' => 'decimal:2',
        'new_rate' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function roomStay()
    {
        return $this->belongsTo(RoomStay::class);
    }

    public function oldRoom()
    {
        return $this->belongsTo(HotelRoom::class, 'old_room_id');
    }

    public function newRoom()
    {
        return $this->belongsTo(HotelRoom::class, 'new_room_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
