<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBranch extends Model {
    use HasFactory;

    protected $fillable = [
        'group_id',
        'branch_code',
        'branch_name',
        'city',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company group this branch belongs to
     */
    public function group() {
        return $this->belongsTo(CompanyGroup::class, 'group_id');
    }

    /**
     * Get all devices for this branch
     */
    public function devices() {
        return $this->hasMany(DeviceMaster::class, 'branch_id');
    }

    /**
     * Get all event settings for this branch
     */
    public function eventSettings() {
        return $this->hasMany(BranchEventSetting::class, 'branch_id');
    }

    /**
     * Get all event logs for this branch
     */
    public function eventLogs() {
        return $this->hasMany(EventLog::class, 'branch_id');
    }

    /**
     * Get all CCTV streams for this branch
     */
    public function cctvStreams() {
        return $this->hasMany(CctvStream::class, 'branch_id');
    }

    /**
     * Get all API credentials for this branch
     */
    public function apiCredentials() {
        return $this->hasMany(ApiCredential::class, 'branch_id');
    }

    /**
     * Get all counting reports for this branch
     */
    public function countingReports() {
        return $this->hasMany(CountingReport::class, 'branch_id');
    }

    /**
     * Get all Re-ID detections for this branch
     */
    public function reIdDetections() {
        return $this->hasMany(ReIdBranchDetection::class, 'branch_id');
    }

    /**
     * Scope: Active branches
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Inactive branches
     */
    public function scopeInactive($query) {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope: By city
     */
    public function scopeByCity($query, $city) {
        return $query->where('city', $city);
    }
}
