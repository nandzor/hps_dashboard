<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CctvPositionSetting extends Model {
    use HasFactory;

    protected $fillable = [
        'layout_id',
        'position_number',
        'branch_id',
        'device_id',
        'position_name',
        'is_enabled',
        'auto_switch',
        'switch_interval',
        'quality',
        'resolution',
    ];

    protected $casts = [
        'position_number' => 'integer',
        'is_enabled' => 'boolean',
        'auto_switch' => 'boolean',
        'switch_interval' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the layout
     */
    public function layout() {
        return $this->belongsTo(CctvLayoutSetting::class, 'layout_id');
    }

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
     * Scope: Enabled positions
     */
    public function scopeEnabled($query) {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope: Auto-switch enabled
     */
    public function scopeAutoSwitch($query) {
        return $query->where('auto_switch', true);
    }

    /**
     * Scope: By position number
     */
    public function scopeByPosition($query, $position) {
        return $query->where('position_number', $position);
    }
}
