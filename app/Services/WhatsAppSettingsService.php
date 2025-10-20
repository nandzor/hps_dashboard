<?php

namespace App\Services;

use App\Models\WhatsAppSettings;
use App\Models\BranchEventSetting;
use Illuminate\Support\Facades\DB;

class WhatsAppSettingsService
{
    /**
     * Get all WhatsApp settings ordered by default first
     */
    public function getAll()
    {
        return WhatsAppSettings::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create new WhatsApp settings
     */
    public function create(array $data)
    {
        // Convert phone numbers string to array
        $phoneNumbers = array_map('trim', explode(',', $data['phone_numbers']));
        $data['phone_numbers'] = array_filter($phoneNumbers, function ($number) {
            return !empty($number);
        });

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;

        return WhatsAppSettings::create($data);
    }

    /**
     * Update WhatsApp settings
     */
    public function update(WhatsAppSettings $whatsappSettings, array $data)
    {
        // Convert phone numbers string to array
        $phoneNumbers = array_map('trim', explode(',', $data['phone_numbers']));
        $data['phone_numbers'] = array_filter($phoneNumbers, function ($number) {
            return !empty($number);
        });

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;

        // Start database transaction
        DB::beginTransaction();

        try {
            // If setting this as default, remove default from others and update branch event settings
            if ($data['is_default']) {
                $this->setAsDefault($whatsappSettings, $data['phone_numbers'], $data['message_template']);
            }

            $whatsappSettings->update($data);

            DB::commit();

            return $whatsappSettings;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Set WhatsApp settings as default and update all related branch event settings
     */
    public function setAsDefault(WhatsAppSettings $whatsappSettings, array $phoneNumbers = null, string $messageTemplate = null)
    {
        // Start database transaction
        DB::beginTransaction();

        try {
            // Set all other settings to not default
            WhatsAppSettings::where('id', '!=', $whatsappSettings->id)
                ->update(['is_default' => false]);

            // Set current setting as default
            $whatsappSettings->update(['is_default' => true]);

            // Use provided data or current setting data
            $phoneNumbers = $phoneNumbers ?? $whatsappSettings->phone_numbers;
            $messageTemplate = $messageTemplate ?? $whatsappSettings->message_template;

            // Update all branch event settings that have whatsapp_enabled = true
            $this->updateBranchEventSettings($phoneNumbers, $messageTemplate);

            DB::commit();

            return $whatsappSettings;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update all branch event settings with WhatsApp enabled
     */
    public function updateBranchEventSettings(array $phoneNumbers, string $messageTemplate)
    {
        return BranchEventSetting::where('whatsapp_enabled', true)
            ->update([
                'whatsapp_numbers' => $phoneNumbers,
                'message_template' => $messageTemplate
            ]);
    }

    /**
     * Delete WhatsApp settings
     */
    public function delete(WhatsAppSettings $whatsappSettings)
    {
        // Prevent deletion of default setting
        if ($whatsappSettings->is_default) {
            throw new \Exception('Cannot delete the default WhatsApp settings.');
        }

        return $whatsappSettings->delete();
    }

    /**
     * Get default WhatsApp settings
     */
    public function getDefault()
    {
        return WhatsAppSettings::default()->active()->first();
    }

    /**
     * Get active WhatsApp settings
     */
    public function getActive()
    {
        return WhatsAppSettings::active()->orderBy('is_default', 'desc')->orderBy('name')->get();
    }
}
