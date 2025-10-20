<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppDeliverySummary extends Model {
    use HasFactory;

    protected $table = 'whatsapp_delivery_summary';

    protected $fillable = [
        'summary_date',
        'branch_id',
        'device_id',
        'total_sent',
        'total_delivered',
        'total_failed',
        'total_pending',
        'avg_delivery_time_ms',
        'unique_recipients',
        'messages_with_image',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'total_sent' => 'integer',
        'total_delivered' => 'integer',
        'total_failed' => 'integer',
        'total_pending' => 'integer',
        'avg_delivery_time_ms' => 'integer',
        'unique_recipients' => 'integer',
        'messages_with_image' => 'integer',
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
     * Scope: By date range
     */
    public function scopeDateRange($query, $startDate, $endDate) {
        return $query->whereBetween('summary_date', [$startDate, $endDate]);
    }

    /**
     * Get delivery rate percentage
     */
    public function getDeliveryRateAttribute() {
        if ($this->total_sent == 0) {
            return 0;
        }
        return round(($this->total_delivered / $this->total_sent) * 100, 2);
    }

    /**
     * Get failure rate percentage
     */
    public function getFailureRateAttribute() {
        if ($this->total_sent == 0) {
            return 0;
        }
        return round(($this->total_failed / $this->total_sent) * 100, 2);
    }
}
