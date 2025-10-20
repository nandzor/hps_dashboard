<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class TestController extends BaseController {
    /**
     * Test endpoint untuk static token
     */
    public function index() {
        return $this->successResponse([
            'authenticated' => true,
            'timestamp' => now()->toDateTimeString(),
            'server_time' => now()->format('Y-m-d H:i:s'),
        ], 'Static token authentication berhasil!');
    }

    /**
     * Test endpoint dengan parameter
     */
    public function show($id) {
        return $this->successResponse([
            'id' => $id,
            'name' => 'Test Item ' . $id,
            'description' => 'This is a test item',
            'created_at' => now()->subDays(rand(1, 30))->toDateTimeString(),
        ], 'Test data retrieved');
    }

    /**
     * Test endpoint POST
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        return $this->createdResponse([
            'id' => rand(100, 999),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_at' => now()->toDateTimeString(),
        ], 'Data created successfully');
    }

    /**
     * Test ping endpoint
     */
    public function ping() {
        return $this->successResponse([
            'timestamp' => microtime(true),
        ], 'pong');
    }

    /**
     * Test echo endpoint
     */
    public function echo(Request $request) {
        return $this->successResponse([
            'your_data' => $request->all(),
            'headers' => [
                'content_type' => $request->header('Content-Type'),
                'authorization' => $request->bearerToken() ? 'Bearer ***' : null,
            ],
        ], 'Echo response');
    }
}
