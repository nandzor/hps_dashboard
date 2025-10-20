<?php

namespace App\Http\Controllers;

use App\Models\ApiCredential;
use App\Services\ApiCredentialService;
use Illuminate\Http\Request;

class ApiCredentialController extends Controller {
    protected $apiCredentialService;

    public function __construct(ApiCredentialService $apiCredentialService) {
        $this->apiCredentialService = $apiCredentialService;
    }

    /**
     * Display a listing of API credentials
     */
    public function index(Request $request) {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $credentials = $this->apiCredentialService->getPaginate($search, $perPage);
        $statistics = $this->apiCredentialService->getStatistics();

        return view('api-credentials.index', compact('credentials', 'statistics', 'search', 'perPage'));
    }

    /**
     * Show the form for creating a new API credential
     */
    public function create() {
        return view('api-credentials.create');
    }

    /**
     * Store a newly created API credential
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'credential_name' => 'required|string|max:150',
            'expires_at' => 'nullable|date|after:today',
            'status' => 'required|in:active,inactive',
        ]);

        // Set defaults for global access
        $validated['branch_id'] = null;  // Global
        $validated['device_id'] = null;  // Global
        $validated['permissions'] = [
            'read' => true,
            'write' => true,
            'delete' => true,
        ];
        $validated['rate_limit'] = 10000;  // High limit for global access

        try {
            $credential = $this->apiCredentialService->createCredential($validated);

            // Pass API secret to session (only shown once)
            return redirect()->route('api-credentials.show', $credential)
                ->with('success', 'API Credential created successfully. Please save the API secret as it will not be shown again.')
                ->with('api_secret', $credential->api_secret);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to create credential: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified API credential
     */
    public function show(ApiCredential $apiCredential) {
        $apiCredential->load(['branch', 'device', 'creator']);

        return view('api-credentials.show', compact('apiCredential'));
    }

    /**
     * Show test page for API credential
     */
    public function test(ApiCredential $apiCredential) {
        return view('api-credentials.test', compact('apiCredential'));
    }

    /**
     * Show the form for editing the specified API credential
     */
    public function edit(ApiCredential $apiCredential) {
        $apiCredential->load(['branch', 'device']);

        return view('api-credentials.edit', compact('apiCredential'));
    }

    /**
     * Update the specified API credential
     */
    public function update(Request $request, ApiCredential $apiCredential) {
        $validated = $request->validate([
            'credential_name' => 'required|string|max:150',
            'expires_at' => 'nullable|date',
            'status' => 'required|in:active,inactive,expired',
            'regenerate_secret' => 'boolean',
        ]);

        // Keep existing scope and permissions (don't change them)
        $validated['branch_id'] = null;  // Always global
        $validated['device_id'] = null;  // Always global
        $validated['permissions'] = [
            'read' => true,
            'write' => true,
            'delete' => true,
        ];
        $validated['rate_limit'] = 10000;  // High limit

        try {
            $this->apiCredentialService->updateCredential($apiCredential, $validated);

            return redirect()->route('api-credentials.show', $apiCredential)
                ->with('success', 'API Credential updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update credential: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified API credential
     */
    public function destroy(ApiCredential $apiCredential) {
        try {
            $apiCredential->delete();

            return redirect()->route('api-credentials.index')
                ->with('success', 'API Credential deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete credential: ' . $e->getMessage());
        }
    }
}
