<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model {
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'device_id',
        're_id',
        'event_type',
        'detected_count',
        'image_path',
        'image_sent',
        'message_sent',
        'notification_sent',
        'event_data',
        'event_timestamp',
    ];

    protected $casts = [
        'detected_count' => 'integer',
        'image_sent' => 'boolean',
        'message_sent' => 'boolean',
        'notification_sent' => 'boolean',
        'event_data' => 'array', // JSONB
        'event_timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the branch
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get the device
     */
    public function device() {
        return $this->belongsTo(DeviceMaster::class, 'device_id', 'device_id');
    }

    /**
     * Get the Re-ID master (person)
     */
    public function reIdMaster() {
        return $this->belongsTo(ReIdMaster::class, 're_id', 're_id');
    }

    /**
     * Scope: By event type
     */
    public function scopeByType($query, $type) {
        return $query->where('event_type', $type);
    }

    /**
     * Scope: Today's events
     */
    public function scopeToday($query) {
        return $query->whereDate('event_timestamp', today());
    }

    /**
     * Scope: Date range
     */
    public function scopeDateRange($query, $startDate, $endDate) {
        return $query->whereBetween('event_timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope: Notifications sent
     */
    public function scopeNotificationSent($query) {
        return $query->where('notification_sent', true);
    }
}
