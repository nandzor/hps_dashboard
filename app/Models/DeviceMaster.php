<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\EncryptionHelper;

class DeviceMaster extends Model {
    use HasFactory;

    protected $fillable = [
        'device_id',
        'device_name',
        'device_type',
        'branch_id',
        'url',
        'username',
        'password',
        'notes',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password', // Hide password from JSON responses
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName() {
        return 'device_id';
    }

    /**
     * Get the branch this device belongs to
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get all event settings for this device
     */
    public function eventSettings() {
        return $this->hasMany(BranchEventSetting::class, 'device_id', 'device_id');
    }

    /**
     * Get all event logs for this device
     */
    public function eventLogs() {
        return $this->hasMany(EventLog::class, 'device_id', 'device_id');
    }

    /**
     * Get all CCTV streams for this device
     */
    public function cctvStreams() {
        return $this->hasMany(CctvStream::class, 'device_id', 'device_id');
    }

    /**
     * Get all Re-ID detections for this device
     */
    public function reIdDetections() {
        return $this->hasMany(ReIdBranchDetection::class, 'device_id', 'device_id');
    }

    /**
     * Get all API credentials for this device
     */
    public function apiCredentials() {
        return $this->hasMany(ApiCredential::class, 'device_id', 'device_id');
    }

    /**
     * Encrypt password before saving (if env enabled)
     */
    public function setPasswordAttribute($value) {
        if ($value && env('ENCRYPT_DEVICE_CREDENTIALS', false)) {
            $this->attributes['password'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Decrypt password when accessing (if env enabled)
     */
    public function getPasswordAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Encrypt username before saving (if env enabled)
     */
    public function setUsernameAttribute($value) {
        if ($value) {
            $this->attributes['username'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['username'] = $value;
        }
    }

    /**
     * Decrypt username when accessing (if env enabled)
     */
    public function getUsernameAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Encrypt URL before saving (if env enabled)
     */
    public function setUrlAttribute($value) {
        if ($value) {
            $this->attributes['url'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['url'] = $value;
        }
    }

    /**
     * Decrypt URL when accessing (if env enabled)
     */
    public function getUrlAttribute($value) {
        if ($value && env('ENCRYPT_DEVICE_CREDENTIALS', false)) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Scope: Active devices
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Inactive devices
     */
    public function scopeInactive($query) {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope: By device type
     */
    public function scopeByType($query, $type) {
        return $query->where('device_type', $type);
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot() {
        parent::boot();

        // Auto-create CCTV stream when device_type is 'cctv'
        static::created(function ($device) {
            if ($device->device_type === 'cctv') {
                $device->createCctvStream();
            }
        });

        // Auto-update CCTV stream when device is updated
        static::updated(function ($device) {
            if ($device->device_type === 'cctv' && $device->wasChanged(['device_name', 'url', 'username', 'password'])) {
                $device->updateCctvStream();
            }
        });

        // Auto-delete CCTV streams when device is deleted
        static::deleting(function ($device) {
            if ($device->device_type === 'cctv') {
                $device->cctvStreams()->delete();
            }
        });
    }

    /**
     * Create CCTV stream for this device
     */
    public function createCctvStream() {
        // Check if stream already exists
        if ($this->cctvStreams()->exists()) {
            return;
        }

        // Generate stream name from device name
        $streamName = $this->device_name . ' Stream';

        // Extract stream protocol from URL
        $streamProtocol = 'rtsp';
        if ($this->url) {
            if (strpos($this->url, 'rtsp://') === 0) {
                $streamProtocol = 'rtsp';
            } elseif (strpos($this->url, 'rtmp://') === 0) {
                $streamProtocol = 'rtmp';
            } elseif (strpos($this->url, 'http://') === 0 || strpos($this->url, 'https://') === 0) {
                $streamProtocol = 'http';
            }
        }

        // Create the CCTV stream
        $this->cctvStreams()->create([
            'branch_id' => $this->branch_id,
            'stream_name' => $streamName,
            'stream_url' => $this->url,
            'stream_username' => $this->username,
            'stream_password' => $this->password,
            'stream_protocol' => $streamProtocol,
            'position' => 1, // Default position
            'resolution' => '1920x1080', // Default resolution
            'fps' => 30, // Default FPS
            'status' => $this->status === 'active' ? 'online' : 'offline',
        ]);
    }

    /**
     * Update CCTV stream for this device
     */
    public function updateCctvStream() {
        $stream = $this->cctvStreams()->first();

        if ($stream) {
            // Update stream with device data
            $stream->update([
                'stream_name' => $this->device_name . ' Stream',
                'stream_url' => $this->url,
                'stream_username' => $this->username,
                'stream_password' => $this->password,
                'status' => $this->status === 'active' ? 'online' : 'offline',
            ]);
        }
    }

    /**
     * Check if this device is a CCTV device
     */
    public function isCctvDevice() {
        return $this->device_type === 'cctv';
    }
}
