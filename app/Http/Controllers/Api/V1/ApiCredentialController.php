<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ApiCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ApiCredentialController extends BaseController
{
    protected $apiCredentialService;

    public function __construct(ApiCredentialService $apiCredentialService)
    {
        $this->apiCredentialService = $apiCredentialService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            $perPage = $request->get('per_page', 15);
            $filters = $request->only(['status', 'branch_id', 'device_id']);

            $credentials = $this->apiCredentialService->getPaginate($search, $perPage, $filters);

            return $this->paginatedResponse($credentials, 'API credentials retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve API credentials');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'credential_name' => 'required|string|max:150',
            'expires_at' => 'nullable|date|after:today',
            'status' => 'required|in:active,inactive',
            'branch_id' => 'nullable|exists:company_branches,id',
            'device_id' => 'nullable|exists:device_masters,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        try {
            $data = $request->only(['credential_name', 'expires_at', 'status', 'branch_id', 'device_id']);

            // Set permissions and rate limit
            $data['permissions'] = [
                'read' => true,
                'write' => true,
                'delete' => true,
            ];
            $data['rate_limit'] = 10000;

            $credential = $this->apiCredentialService->createCredential($data);

            return $this->createdResponse($credential->load(['branch', 'device', 'creator']), 'API credential created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create API credential');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $credential = $this->apiCredentialService->getWithRelations($id);
            return $this->successResponse($credential, 'API credential retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('API credential not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $credential = $this->apiCredentialService->findById($id);

            if (!$credential) {
                return $this->notFoundResponse('API credential not found');
            }

            $validator = Validator::make($request->all(), [
                'credential_name' => 'sometimes|required|string|max:150',
                'expires_at' => 'nullable|date',
                'status' => 'sometimes|required|in:active,inactive,expired',
                'regenerate_secret' => 'boolean',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $data = $request->only(['credential_name', 'expires_at', 'status', 'regenerate_secret']);
            $this->apiCredentialService->updateCredential($credential, $data);

            return $this->successResponse($credential->fresh()->load(['branch', 'device', 'creator']), 'API credential updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update API credential');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $credential = $this->apiCredentialService->findById($id);

            if (!$credential) {
                return $this->notFoundResponse('API credential not found');
            }

            $this->apiCredentialService->delete($credential);

            return $this->successResponse(null, 'API credential deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete API credential');
        }
    }

    /**
     * Test API credential
     */
    public function test($id)
    {
        try {
            $credential = $this->apiCredentialService->findById($id);

            if (!$credential) {
                return $this->notFoundResponse('API credential not found');
            }

            // Test database connection
            $dbTest = $this->testDatabaseConnection();

            // Test Redis connection
            $redisTest = $this->testRedisConnection();

            // Test API key validation
            $apiKeyTest = $this->testApiKeyValidation($credential);

            // Test sample API endpoint
            $endpointTest = $this->testSampleEndpoint($credential);

            $results = [
                'database' => $dbTest,
                'redis' => $redisTest,
                'api_key' => $apiKeyTest,
                'sample_endpoint' => $endpointTest,
            ];

            $allPassed = collect($results)->every(fn($test) => $test['status'] === 'success');

            return $this->successResponse([
                'credential' => $credential->load(['branch', 'device', 'creator']),
                'test_results' => $results,
                'overall_status' => $allPassed ? 'success' : 'partial_failure',
                'tested_at' => now()->toISOString(),
            ], $allPassed
                ? 'All tests passed successfully'
                : 'Some tests failed, check individual results');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('API credential test failed');
        }
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'success',
                'message' => 'Database connection successful',
                'details' => 'Connected to PostgreSQL database',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test Redis connection
     */
    private function testRedisConnection(): array
    {
        try {
            app('redis')->ping();
            return [
                'status' => 'success',
                'message' => 'Redis connection successful',
                'details' => 'Connected to Redis server',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Redis connection failed',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test API key validation
     */
    private function testApiKeyValidation($credential): array
    {
        try {
            // Test if API key and secret are properly formatted
            $keyValid = !empty($credential->api_key) && strlen($credential->api_key) >= 10;
            $secretValid = !empty($credential->api_secret) && strlen($credential->api_secret) >= 10;

            if ($keyValid && $secretValid) {
                return [
                    'status' => 'success',
                    'message' => 'API key validation successful',
                    'details' => 'API key and secret are properly formatted and accessible',
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'API key validation failed',
                    'details' => 'API key or secret is missing or improperly formatted',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'API key validation failed',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test sample API endpoint
     */
    private function testSampleEndpoint($credential): array
    {
        try {
            // Use internal container URL for testing
            $baseUrl = config('app.url');
            if (str_contains($baseUrl, 'localhost')) {
                $baseUrl = 'http://cctv_app_staging:80';
            }

            // Test a simple API endpoint using the credential
            $response = Http::withHeaders([
                'X-API-Key' => $credential->api_key,
                'X-API-Secret' => $credential->api_secret,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->timeout(5)->get($baseUrl . '/api/v1/detections');

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Sample endpoint test successful',
                    'details' => 'Successfully called /api/v1/detections endpoint',
                    'response_status' => $response->status(),
                ];
            } else {
                // Sanitize response body to avoid non-ISO-8859-1 characters
                $responseBody = $response->body();
                $sanitizedBody = mb_convert_encoding($responseBody, 'UTF-8', 'UTF-8');
                $sanitizedBody = preg_replace('/[^\x20-\x7E]/', '', $sanitizedBody);

                return [
                    'status' => 'error',
                    'message' => 'Sample endpoint test failed',
                    'details' => 'Failed to call sample endpoint',
                    'response_status' => $response->status(),
                    'response_body' => substr($sanitizedBody, 0, 500), // Limit to 500 chars
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Sample endpoint test failed',
                'details' => $e->getMessage(),
            ];
        }
    }
}
