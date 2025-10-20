<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StorageFile extends Model {
    use HasFactory;

    protected $fillable = [
        'file_path',
        'file_type',
        'file_size',
        'storage_disk',
        'related_table',
        'related_id',
        'metadata',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array', // JSONB
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who uploaded this file
     */
    public function uploader() {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the related model (polymorphic-like but manual)
     */
    public function getRelatedModel() {
        if (!$this->related_table || !$this->related_id) {
            return null;
        }

        $modelClass = 'App\\Models\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->related_table)));

        if (class_exists($modelClass)) {
            return $modelClass::find($this->related_id);
        }

        return null;
    }

    /**
     * Get full file URL
     */
    public function getUrlAttribute() {
        return Storage::disk($this->storage_disk)->url($this->file_path);
    }

    /**
     * Get file size in human-readable format
     */
    public function getHumanSizeAttribute() {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete file from storage
     */
    public function deleteFile() {
        if (Storage::disk($this->storage_disk)->exists($this->file_path)) {
            Storage::disk($this->storage_disk)->delete($this->file_path);
        }

        return $this->delete();
    }

    /**
     * Scope: By related table
     */
    public function scopeByRelatedTable($query, $table) {
        return $query->where('related_table', $table);
    }

    /**
     * Scope: By storage disk
     */
    public function scopeByDisk($query, $disk) {
        return $query->where('storage_disk', $disk);
    }

    /**
     * Scope: Old files (older than X days)
     */
    public function scopeOlderThan($query, $days) {
        return $query->where('created_at', '<', now()->subDays($days));
    }
}
