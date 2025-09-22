<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_uz',
        'name_ru',
        'code',
        'color',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get contracts with this status
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'status_id');
    }

    /**
     * Get active statuses
     */
    public static function active()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Get status badge HTML
     */
    public function getBadgeAttribute()
    {
        $colorClasses = [
            'success' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'danger' => 'bg-red-100 text-red-800',
            'info' => 'bg-blue-100 text-blue-800',
        ];

        $class = $colorClasses[$this->color] ?? 'bg-gray-100 text-gray-800';

        return "<span class='px-3 py-1 rounded-full text-sm font-medium {$class}'>{$this->name_uz}</span>";
    }
}
