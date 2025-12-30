<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MaintenanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id',
        'hotel_room_id',
        'request_number',
        'title',
        'description',
        'location',
        'category',
        'priority',
        'status',
        'reported_by',
        'assigned_to',
        'reported_at',
        'acknowledged_at',
        'started_at',
        'completed_at',
        'estimated_cost',
        'actual_cost',
        'notes',
        'completion_notes',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate request number
        static::creating(function ($model) {
            if (!$model->request_number) {
                $model->request_number = 'MNT-' . strtoupper(Str::random(8));
            }
            if (!$model->reported_at) {
                $model->reported_at = now();
            }
        });
    }

    /**
     * Get the property that owns the maintenance request.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the hotel room for this request (if applicable).
     */
    public function hotelRoom()
    {
        return $this->belongsTo(HotelRoom::class);
    }

    /**
     * Get the user who reported this request.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the user assigned to this request.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in progress requests.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for urgent priority.
     */
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    /**
     * Scope for high priority.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['urgent', 'high']);
    }

    /**
     * Get status label with color.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'acknowledged' => 'Acknowledged',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'acknowledged' => 'blue',
            'in_progress' => 'indigo',
            'completed' => 'green',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Get priority color for UI.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'plumbing' => 'Plumbing',
            'electrical' => 'Electrical',
            'hvac' => 'HVAC/AC',
            'furniture' => 'Furniture',
            'electronics' => 'Electronics',
            'cleaning' => 'Cleaning',
            'painting' => 'Painting',
            'other' => 'Other',
            default => ucfirst($this->category),
        };
    }
}
