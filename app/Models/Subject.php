<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_legal_entity', 'org_form_id', 'company_name', 'inn', 'is_resident',
        'country_code', 'oked', 'bank_name', 'bank_code', 'bank_account',
        'legal_address', 'document_type', 'document_series', 'document_number',
        'issued_by', 'issued_date', 'pinfl', 'phone', 'email', 'physical_address'
    ];

    protected $casts = [
        'is_legal_entity' => 'boolean',
        'is_resident' => 'boolean',
        'issued_date' => 'date'
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function objects()
    {
        return $this->hasMany(Object::class);
    }

    public function getDisplayNameAttribute()
    {
        return $this->is_legal_entity ? $this->company_name : $this->full_name;
    }

    public function getIdentifierAttribute()
    {
        return $this->is_legal_entity ? $this->inn : $this->pinfl;
    }
}
