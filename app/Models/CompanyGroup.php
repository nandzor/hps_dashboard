<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyGroup extends Model {
    use HasFactory;

    protected $fillable = [
        'province_code',
        'province_name',
        'group_name',
        'address',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all branches for this group
     */
    public function branches() {
        return $this->hasMany(CompanyBranch::class, 'group_id');
    }

    /**
     * Get active branches only
     */
    public function activeBranches() {
        return $this->branches()->where('status', 'active');
    }

    /**
     * Scope: Active groups
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Inactive groups
     */
    public function scopeInactive($query) {
        return $query->where('status', 'inactive');
    }
}
