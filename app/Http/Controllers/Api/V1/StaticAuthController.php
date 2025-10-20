<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;

class StaticAuthController extends BaseController {
    /**
     * Validate static token
     */
    public function validate(Request $request) {
        return $this->successResponse([
            'timestamp' => now()->toDateTimeString()
        ], 'Token is valid');
    }

    /**
     * Get API info
     */
    public function info() {
        return $this->successResponse([
            'api_version' => '1.0',
            'app_name' => config('app.name'),
            'endpoints' => [
                'test' => [
                    'main' => 'GET /api/static/test',
                    'ping' => 'GET /api/static/test/ping',
                    'echo' => 'POST /api/static/test/echo',
                    'show' => 'GET /api/static/test/{id}',
                    'create' => 'POST /api/static/test',
                ],
            ],
            'authentication' => 'Bearer Token (Static)',
            'header_required' => 'Authorization: Bearer your-static-token'
        ], 'API Information');
    }
}
