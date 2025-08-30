<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractStatus extends Model
{
    protected $fillable = ['name_uz', 'name_ru', 'code', 'color', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'status_id');
    }
}
