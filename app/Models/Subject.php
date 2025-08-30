<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    protected $fillable = [
        'is_legal_entity',
        'org_form_id',
        'company_name',
        'inn',
        'is_resident',
        'country_code',
        'oked',
        'bank_name',
        'bank_code',
        'bank_account',
        'legal_address',
        'document_type',
        'document_series',
        'document_number',
        'issued_by',
        'issued_date',
        'pinfl',
        'phone',
        'email',
        'physical_address',
        'is_active'
    ];

    protected $casts = [
        'is_legal_entity' => 'boolean',
        'is_resident' => 'boolean',
        'issued_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function orgForm(): BelongsTo
    {
        return $this->belongsTo(OrgForm::class, 'org_form_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function objects(): HasMany
    {
        return $this->hasMany(Objectt::class);
    }

    // Accessor for display name
    public function getDisplayNameAttribute(): string
    {
        if ($this->is_legal_entity) {
            return $this->company_name ?? 'Не указано';
        }

        $parts = array_filter([
            $this->last_name ?? '',
            $this->first_name ?? '',
            $this->father_name ?? ''
        ]);

        return !empty($parts) ? implode(' ', $parts) : 'Не указано';
    }

    // Accessor for identifier (INN or PINFL)
    public function getIdentifierAttribute(): string
    {
        return $this->is_legal_entity ? ($this->inn ?? '') : ($this->pinfl ?? '');
    }
}
