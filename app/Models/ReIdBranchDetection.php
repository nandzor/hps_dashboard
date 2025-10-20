<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReIdBranchDetection extends Model {
    use HasFactory;

    protected $fillable = [
        're_id',
        'branch_id',
        'device_id',
        'detection_timestamp',
        'detected_count',
        'detection_data',
    ];

    protected $casts = [
        'detection_timestamp' => 'datetime',
        'detected_count' => 'integer',
        'detection_data' => 'array', // JSONB
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the Re-ID master record
     */
    public function reIdMaster() {
        return $this->belongsTo(ReIdMaster::class, 're_id', 're_id');
    }

    /**
     * Get the branch where detection occurred
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get the device that detected
     */
    public function device() {
        return $this->belongsTo(DeviceMaster::class, 'device_id', 'device_id');
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $startDate, $endDate) {
        return $query->whereBetween('detection_timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope: Today's detections
     */
    public function scopeToday($query) {
        return $query->whereDate('detection_timestamp', today());
    }

    /**
     * Scope: By Re-ID
     */
    public function scopeByReId($query, $reId) {
        return $query->where('re_id', $reId);
    }
}
