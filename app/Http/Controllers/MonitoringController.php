<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class MonitoringController extends Controller {
    /**
     * Queue Status Endpoint
     */
    public function queueStatus() {
        try {
            // Get queue sizes
            $queueSizes = [
                'default' => Redis::llen('queues:default'),
                'high' => Redis::llen('queues:high'),
                'low' => Redis::llen('queues:low'),
            ];

            // Get failed jobs count
            $failedJobs = DB::table('failed_jobs')->count();

            // Get system info
            $systemInfo = [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timestamp' => now()->toISOString(),
            ];

            // Get FrankenPHP info
            $frankenphpInfo = [
                'workers' => 16,
                'schedulers' => 2,
                'queues' => ['default', 'high', 'low'],
            ];

            return response()->json([
                'status' => 'healthy',
                'queue_sizes' => $queueSizes,
                'failed_jobs' => $failedJobs,
                'system' => $systemInfo,
                'frankenphp' => $frankenphpInfo,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * System Health Check
     */
    public function health() {
        try {
            // Check database connection
            DB::connection()->getPdo();
            $dbStatus = 'connected';

            // Check Redis connection
            Redis::ping();
            $redisStatus = 'connected';

            return response()->json([
                'status' => 'healthy',
                'database' => $dbStatus,
                'redis' => $redisStatus,
                'timestamp' => now()->toISOString(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }
}
