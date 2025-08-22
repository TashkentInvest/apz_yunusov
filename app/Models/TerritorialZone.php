<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerritorialZone extends Model
{
    use HasFactory;

    protected $fillable = ['name_uz', 'name_ru', 'code', 'coefficient', 'is_active'];

    protected $casts = [
        'coefficient' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function objects()
    {
        return $this->hasMany(Objectt::class);
    }
}
