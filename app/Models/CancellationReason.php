<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationReason extends Model
{
    use HasFactory;

    protected $fillable = ['name_uz', 'name_ru', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function cancellations()
    {
        return $this->hasMany(ContractCancellation::class);
    }
}
