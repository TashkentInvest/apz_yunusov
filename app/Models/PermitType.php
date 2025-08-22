<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitType extends Model
{
    use HasFactory;

    protected $fillable = ['name_uz', 'name_ru', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function objects()
    {
        return $this->hasMany(Objectt::class);
    }
}
