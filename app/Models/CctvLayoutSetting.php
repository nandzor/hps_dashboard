<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CctvLayoutSetting extends Model {
    use HasFactory;

    protected $fillable = [
        'layout_name',
        'layout_type',
        'description',
        'total_positions',
        'is_default',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'total_positions' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who created this layout
     */
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all position settings for this layout
     * Ordered by position_number
     */
    public function positions() {
        return $this->hasMany(CctvPositionSetting::class, 'layout_id')
            ->orderBy('position_number', 'asc');
    }

    /**
     * Scope: Default layout
     */
    public function scopeDefault($query) {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Active layouts
     */
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By layout type
     */
    public function scopeByType($query, $type) {
        return $query->where('layout_type', $type);
    }
}
