<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchEventSetting extends Model {
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'device_id',
        'is_active',
        'send_image',
        'send_message',
        'whatsapp_enabled',
        'whatsapp_numbers',
        'message_template',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'send_image' => 'boolean',
        'send_message' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'whatsapp_numbers' => 'array', // JSONB
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Scope: Active settings
     */
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    /**
     * Scope: WhatsApp enabled
     */
    public function scopeWhatsAppEnabled($query) {
        return $query->where('whatsapp_enabled', true);
    }

    /**
     * Get formatted message with template variables replaced
     */
    public function formatMessage(array $variables = []) {
        $message = $this->message_template;

        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }
}
