<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Helpers\EncryptionHelper;

class ApiCredential extends Model {
    use HasFactory;

    protected $fillable = [
        'credential_name',
        'api_key',
        'api_secret',
        'branch_id',
        'device_id',
        'permissions',
        'rate_limit',
        'expires_at',
        'last_used_at',
        'status',
        'created_by',
    ];

    protected $casts = [
        'permissions' => 'array', // JSONB
        'rate_limit' => 'integer',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'api_secret', // Hide secret from JSON responses
    ];

    /**
     * Get the decrypted API key
     */
    public function getApiKeyAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Set the encrypted API key
     */
    public function setApiKeyAttribute($value) {
        if ($value) {
            $this->attributes['api_key'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['api_key'] = $value;
        }
    }

    /**
     * Get the decrypted API secret
     */
    public function getApiSecretAttribute($value) {
        if ($value) {
            return EncryptionHelper::decrypt($value);
        }
        return $value;
    }

    /**
     * Set the encrypted API secret
     */
    public function setApiSecretAttribute($value) {
        if ($value) {
            $this->attributes['api_secret'] = EncryptionHelper::encrypt($value);
        } else {
            $this->attributes['api_secret'] = $value;
        }
    }

    /**
     * Get the raw encrypted API key (for database operations)
     */
    public function getRawApiKeyAttribute() {
        return $this->getRawOriginal('api_key');
    }

    /**
     * Get the raw encrypted API secret (for database operations)
     */
    public function getRawApiSecretAttribute() {
        return $this->getRawOriginal('api_secret');
    }

    /**
     * Get the branch (if scoped)
     */
    public function branch() {
        return $this->belongsTo(CompanyBranch::class, 'branch_id');
    }

    /**
     * Get the device (if scoped)
     */
    public function device() {
        return $this->belongsTo(DeviceMaster::class, 'device_id', 'device_id');
    }

    /**
     * Get the user who created this credential
     */
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get API usage summaries
     */
    public function usageSummaries() {
        return $this->hasMany(ApiUsageSummary::class, 'api_credential_id');
    }

    /**
     * Generate API key
     */
    public static function generateApiKey($prefix = 'cctv') {
        return $prefix . '_' . Str::random(32);
    }

    /**
     * Generate API secret
     */
    public static function generateApiSecret() {
        return Str::random(64);
    }

    /**
     * Check if credential is expired
     */
    public function isExpired() {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if credential is active
     */
    public function isActive() {
        return $this->status === 'active' && !$this->isExpired();
    }

    /**
     * Scope: Active credentials
     */
    public function scopeActive($query) {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Expired credentials
     */
    public function scopeExpired($query) {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Find credential by API key (handles encrypted data)
     */
    public static function findByApiKey($apiKey) {
        // Get all active credentials and check decrypted values
        $credentials = static::where('status', 'active')->get();

        foreach ($credentials as $credential) {
            if ($credential->api_key === $apiKey && !$credential->isExpired()) {
                return $credential;
            }
        }

        return null;
    }

    /**
     * Verify API credentials
     */
    public function verifyCredentials($apiKey, $apiSecret) {
        return $this->api_key === $apiKey && $this->api_secret === $apiSecret;
    }

    /**
     * Get decrypted API key for display (show first 8 characters + dots)
     */
    public function getMaskedApiKeyAttribute() {
        $apiKey = $this->api_key;

        // If decryption failed or empty, try to get raw value
        if (empty($apiKey)) {
            $rawValue = $this->getRawOriginal('api_key');
            if (!empty($rawValue)) {
                // Try to decrypt raw value
                try {
                    $apiKey = EncryptionHelper::decrypt($rawValue);
                } catch (\Exception $e) {
                    // If decryption fails, show masked raw value
                    return substr($rawValue, 0, 8) . '....';
                }
            }
        }

        if (empty($apiKey)) {
            return '****....';
        }

        if (strlen($apiKey) <= 8) {
            return str_repeat('*', strlen($apiKey));
        }

        return substr($apiKey, 0, 8) . '....';
    }

    /**
     * Get decrypted API secret for display (show first 8 characters + dots)
     */
    public function getMaskedApiSecretAttribute() {
        $apiSecret = $this->api_secret;

        // If decryption failed or empty, try to get raw value
        if (empty($apiSecret)) {
            $rawValue = $this->getRawOriginal('api_secret');
            if (!empty($rawValue)) {
                // Try to decrypt raw value
                try {
                    $apiSecret = EncryptionHelper::decrypt($rawValue);
                } catch (\Exception $e) {
                    // If decryption fails, show masked raw value
                    return substr($rawValue, 0, 8) . '....';
                }
            }
        }

        if (empty($apiSecret)) {
            return '****....';
        }

        if (strlen($apiSecret) <= 8) {
            return str_repeat('*', strlen($apiSecret));
        }

        return substr($apiSecret, 0, 8) . '....';
    }
}
