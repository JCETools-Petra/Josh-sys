<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HkTask extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'property_id',
        'hotel_room_id',
        'assigned_to',
        'task_date',
        'task_type',
        'priority',
        'status',
        'checklist',
        'completed_items',
        'started_at',
        'completed_at',
        'duration_minutes',
        'inspected_by',
        'inspected_at',
        'quality_score',
        'inspection_notes',
        'photos',
        'notes',
    ];

    protected $casts = [
        'task_date' => 'date',
        'checklist' => 'array',
        'completed_items' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'inspected_at' => 'datetime',
        'photos' => 'array',
        'duration_minutes' => 'integer',
        'quality_score' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function inspectedBy()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    /**
     * Scope for today's tasks.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('task_date', today());
    }

    /**
     * Scope for pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Calculate duration when completing task.
     */
    public function markAsCompleted()
    {
        $this->completed_at = now();
        if ($this->started_at) {
            $this->duration_minutes = $this->started_at->diffInMinutes($this->completed_at);
        }
        $this->status = 'completed';
        $this->save();
    }

    /**
     * Get default checklist based on task type.
     */
    public static function getDefaultChecklist($taskType)
    {
        $checklists = [
            'daily_cleaning' => [
                'Ganti linen tempat tidur',
                'Bersihkan kamar mandi',
                'Vacuum/pel lantai',
                'Bersihkan debu furniture',
                'Isi amenities (sabun, shampo, dll)',
                'Buang sampah',
                'Rapikan meja kerja',
                'Cek minibar & isi ulang',
                'Cek kondisi AC/TV/lampu',
            ],
            'deep_cleaning' => [
                'Cuci gorden',
                'Bersihkan jendela',
                'Bersihkan AC filter',
                'Vacuum sofa & karpet detail',
                'Bersihkan lemari dalam',
                'Polish furniture',
                'Bersihkan dinding',
                'Disinfeksi kamar mandi detail',
            ],
            'turndown' => [
                'Lipat sprei',
                'Taruh coklat di bantal',
                'Tutup gorden',
                'Nyalakan lampu malam',
                'Isi air mineral',
                'Rapikan kamar',
            ],
            'inspection' => [
                'Cek kebersihan kamar',
                'Cek kelengkapan amenities',
                'Cek kondisi furniture',
                'Cek elektronik berfungsi',
                'Cek kamar mandi',
                'Cek kerusakan',
            ],
        ];

        return $checklists[$taskType] ?? [];
    }
}
