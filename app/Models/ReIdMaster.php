<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReIdMaster extends Model {
    use HasFactory;

    protected $fillable = [
        're_id',
        'detection_date',
        'detection_time',
        'person_name',
        'appearance_features',
        'first_detected_at',
        'last_detected_at',
        'total_detection_branch_count',
        'total_actual_count',
        'status',
    ];

    protected $casts = [
        'detection_date' => 'date',
        'detection_time' => 'datetime',
        'appearance_features' => 'array', // JSONB
        'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime',
        'total_detection_branch_count' => 'integer',
        'total_actual_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all branch detections for this person
     */
    public function branchDetections() {
        return $this->hasMany(ReIdBranchDetection::class, 're_id', 're_id');
    }

    /**
     * Get all event logs for this person
     */
    public function eventLogs() {
        return $this->hasMany(EventLog::class, 're_id', 're_id');
    }

    /**
     * Get unique branches that detected this person
     */
    public function detectedBranches() {
        return $this->branchDetections()
            ->with('branch')
            ->select('branch_id', 're_id')
            ->groupBy('branch_id', 're_id')
            ->get()
            ->map(fn($detection) => $detection->branch);
    }

    /**
     * Scope: Active persons
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Inactive persons
     */
    public function scopeInactive($query) {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $startDate, $endDate) {
        return $query->whereBetween('detection_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Recent detections (last 7 days)
     */
    public function scopeRecent($query) {
        return $query->where('detection_date', '>=', now()->subDays(7));
    }
}
