<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppSettings;
use App\Services\WhatsAppSettingsService;
use Illuminate\Http\Request;

class WhatsAppSettingsController extends Controller {
    protected $whatsappSettingsService;

    public function __construct(WhatsAppSettingsService $whatsappSettingsService)
    {
        $this->whatsappSettingsService = $whatsappSettingsService;
    }
    public function index() {
        $whatsappSettings = $this->whatsappSettingsService->getAll();

        return view('whatsapp-settings.index', compact('whatsappSettings'));
    }

    public function create() {
        return view('whatsapp-settings.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:whatsapp_settings,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'phone_numbers' => ['required', 'string'],
            'message_template' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        try {
            $this->whatsappSettingsService->create($data);

            return redirect()->route('whatsapp-settings.index')
                ->with('success', 'WhatsApp settings created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create WhatsApp settings: ' . $e->getMessage());
        }
    }

    public function show(WhatsAppSettings $whatsappSettings) {
        return view('whatsapp-settings.show', compact('whatsappSettings'));
    }

    public function edit(WhatsAppSettings $whatsappSettings) {
        return view('whatsapp-settings.edit', compact('whatsappSettings'));
    }

    public function update(Request $request, WhatsAppSettings $whatsappSettings) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:whatsapp_settings,name,' . $whatsappSettings->id],
            'description' => ['nullable', 'string', 'max:500'],
            'phone_numbers' => ['required', 'string'],
            'message_template' => ['required', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        try {
            $this->whatsappSettingsService->update($whatsappSettings, $data);

            return redirect()->route('whatsapp-settings.show', $whatsappSettings)
                ->with('success', 'WhatsApp settings updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update WhatsApp settings: ' . $e->getMessage());
        }
    }

    public function destroy(WhatsAppSettings $whatsappSettings) {
        try {
            $this->whatsappSettingsService->delete($whatsappSettings);

            return redirect()->route('whatsapp-settings.index')
                ->with('success', 'WhatsApp settings deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    public function setDefault(WhatsAppSettings $whatsappSettings) {
        try {
            $this->whatsappSettingsService->setAsDefault($whatsappSettings);

            return redirect()->back()
                ->with('success', 'Default WhatsApp settings updated successfully. All active WhatsApp notifications have been updated.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update default WhatsApp settings: ' . $e->getMessage());
        }
    }
}
