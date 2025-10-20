<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiUsageSummary extends Model {
    use HasFactory;

    protected $table = 'api_usage_summary';

    protected $fillable = [
        'api_credential_id',
        'summary_date',
        'endpoint',
        'method',
        'total_requests',
        'success_requests',
        'error_requests',
        'avg_response_time_ms',
        'max_response_time_ms',
        'min_response_time_ms',
        'avg_query_count',
        'max_query_count',
        'avg_memory_usage',
        'max_memory_usage',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'total_requests' => 'integer',
        'success_requests' => 'integer',
        'error_requests' => 'integer',
        'avg_response_time_ms' => 'integer',
        'max_response_time_ms' => 'integer',
        'min_response_time_ms' => 'integer',
        'avg_query_count' => 'integer',
        'max_query_count' => 'integer',
        'avg_memory_usage' => 'integer',
        'max_memory_usage' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the API credential
     */
    public function apiCredential() {
        return $this->belongsTo(ApiCredential::class, 'api_credential_id');
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $startDate, $endDate) {
        return $query->whereBetween('summary_date', [$startDate, $endDate]);
    }

    /**
     * Scope: By endpoint
     */
    public function scopeByEndpoint($query, $endpoint) {
        return $query->where('endpoint', $endpoint);
    }

    /**
     * Scope: By method
     */
    public function scopeByMethod($query, $method) {
        return $query->where('method', $method);
    }

    /**
     * Get success rate percentage
     */
    public function getSuccessRateAttribute() {
        if ($this->total_requests == 0) {
            return 0;
        }
        return round(($this->success_requests / $this->total_requests) * 100, 2);
    }
}
