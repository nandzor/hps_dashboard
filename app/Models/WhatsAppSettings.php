<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppSettings extends Model {
    use HasFactory;

    protected $table = 'whatsapp_settings';

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName() {
        return 'id';
    }

    protected $fillable = [
        'name',
        'description',
        'phone_numbers',
        'message_template',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope: Active settings
     */
    public function scopeActive($query) {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Default setting
     */
    public function scopeDefault($query) {
        return $query->where('is_default', true);
    }

    /**
     * Get the default WhatsApp settings
     */
    public static function getDefault() {
        return static::default()->active()->first();
    }

    /**
     * Get all active WhatsApp settings
     */
    public static function getActive() {
        return static::active()->orderBy('is_default', 'desc')->orderBy('name')->get();
    }

    /**
     * Format message with variables
     */
    public function formatMessage(array $variables = []): string {
        $template = $this->message_template;

        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }

        return $template;
    }

    /**
     * Get formatted phone numbers as string
     */
    public function getPhoneNumbersString(): string {
        return implode(', ', $this->phone_numbers);
    }

    /**
     * Set phone numbers from string
     */
    public function setPhoneNumbersFromString(string $numbersString): void {
        $numbers = array_map('trim', explode(',', $numbersString));
        $this->phone_numbers = array_filter($numbers, function ($number) {
            return !empty($number);
        });
    }

    /**
     * Boot method to ensure only one default setting
     */
    protected static function boot() {
        parent::boot();

        static::saving(function ($model) {
            if ($model->is_default) {
                // Remove default flag from other settings
                static::where('id', '!=', $model->id)->update(['is_default' => false]);
            }
        });
    }
}
