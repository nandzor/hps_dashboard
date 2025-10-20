<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountingReport extends Model {
    use HasFactory;

    protected $fillable = [
        'report_type',
        'report_date',
        'branch_id',
        'total_devices',
        'total_detections',
        'total_events',
        'unique_device_count',
        'unique_person_count',
        'report_data',
        'generated_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_devices' => 'integer',
        'total_detections' => 'integer',
        'total_events' => 'integer',
        'unique_device_count' => 'integer',
        'unique_person_count' => 'integer',
        'report_data' => 'array', // JSONB
        'generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the branch (if scoped)
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Scope: By report type
     */
    public function scopeByType($query, $type) {
        return $query->where('report_type', $type);
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $startDate, $endDate) {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Daily reports
     */
    public function scopeDaily($query) {
        return $query->where('report_type', 'daily');
    }

    /**
     * Scope: Monthly reports
     */
    public function scopeMonthly($query) {
        return $query->where('report_type', 'monthly');
    }
}
