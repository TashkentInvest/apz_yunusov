<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectType extends Model
{
    protected $fillable = ['name_uz', 'name_ru', 'coefficient', 'description', 'is_active'];
    protected $casts = [
        'coefficient' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function objects()
    {
        return $this->hasMany(Objectt::class, 'object_type_id');
    }
}
