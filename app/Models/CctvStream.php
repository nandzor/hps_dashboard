<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CctvStream extends Model {
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'device_id',
        'stream_name',
        'stream_url',
        'stream_username',
        'stream_password',
        'stream_protocol',
        'position',
        'resolution',
        'fps',
        'status',
        'last_checked_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'fps' => 'integer',
        'last_checked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'stream_password', // Hide password from JSON responses
        'stream_username', // Hide username from JSON responses
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
     * Encrypt stream URL before saving (if env enabled)
     */
    public function setStreamUrlAttribute($value) {
        if ($value) {
            $this->attributes['stream_url'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['stream_url'] = $value;
        }
    }

    /**
     * Decrypt stream URL when accessing (if env enabled)
     */
    public function getStreamUrlAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Encrypt stream username before saving (if env enabled)
     */
    public function setStreamUsernameAttribute($value) {
        if ($value) {
            $this->attributes['stream_username'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['stream_username'] = $value;
        }
    }

    /**
     * Decrypt stream username when accessing (if env enabled)
     */
    public function getStreamUsernameAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Encrypt stream password before saving (if env enabled)
     */
    public function setStreamPasswordAttribute($value) {
        if ($value) {
            $this->attributes['stream_password'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['stream_password'] = $value;
        }
    }

    /**
     * Decrypt stream password when accessing (if env enabled)
     */
    public function getStreamPasswordAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Encrypt stream protocol before saving (if env enabled)
     */
    public function setStreamProtocolAttribute($value) {
        if ($value) {
            $this->attributes['stream_protocol'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['stream_protocol'] = $value;
        }
    }

    /**
     * Decrypt stream protocol when accessing (if env enabled)
     */
    public function getStreamProtocolAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Scope: Online streams
     */
    public function scopeOnline($query) {
        return $query->where('status', 'online');
    }

    /**
     * Scope: Offline streams
     */
    public function scopeOffline($query) {
        return $query->where('status', 'offline');
    }

    /**
     * Scope: By position
     */
    public function scopeByPosition($query, $position) {
        return $query->where('position', $position);
    }
}
