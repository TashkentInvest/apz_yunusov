<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KengashHulosasi extends Model
{
    use HasFactory;

    protected $table = 'kengash_hulosasi';

    protected $fillable = [
        'number',
        'date',
        'title',
        'description',
        'status',
        'data',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who created this record
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active records
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive records
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for archived records
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Get status badge class for styling
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-yellow-100 text-yellow-800',
            'archived' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get status text in Russian
     */
    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'active' => 'Активный',
            'inactive' => 'Неактивный',
            'archived' => 'Архивный',
            default => 'Неизвестно'
        };
    }
}