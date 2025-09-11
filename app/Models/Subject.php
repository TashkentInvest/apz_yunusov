<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        // Common fields
        'is_legal_entity',
        'phone',
        'email',
        'physical_address',
        'is_active',

        // Legal entity fields
        'company_name',
        'inn',
        'bank_name',
        'bank_code',
        'bank_account',
        'legal_address',
        'oked',
        'is_resident',
        'country_code',
        'org_form_id',

        // Physical person fields
        'first_name',
        'last_name',
        'middle_name',
        'document_type',
        'document_series',
        'document_number',
        'issued_by',
        'issued_date',
        'pinfl',
        'birth_date',
        'gender'
    ];

    protected $casts = [
        'is_legal_entity' => 'boolean',
        'is_active' => 'boolean',
        'is_resident' => 'boolean',
        'issued_date' => 'date',
        'birth_date' => 'date'
    ];

    /**
     * Get display name for the subject
     */
    public function getDisplayNameAttribute()
    {
        if ($this->is_legal_entity) {
            return $this->company_name;
        } else {
            $parts = array_filter([
                $this->last_name,
                $this->first_name,
                $this->middle_name
            ]);

            if (empty($parts)) {
                // Fallback to document info if no names
                return ($this->document_series ? $this->document_series . ' ' : '') . $this->document_number;
            }

            return implode(' ', $parts);
        }
    }

    /**
     * Get identifier (INN for legal entity, PINFL for physical person)
     */
    public function getIdentifierAttribute()
    {
        return $this->is_legal_entity ? $this->inn : $this->pinfl;
    }

    /**
     * Relationship with contracts
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Relationship with objects
     */
    public function objects()
    {
        return $this->hasMany(Objectt::class);
    }

    /**
     * Scope for active subjects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for legal entities
     */
    public function scopeLegalEntities($query)
    {
        return $query->where('is_legal_entity', true);
    }

    /**
     * Scope for physical persons
     */
    public function scopePhysicalPersons($query)
    {
        return $query->where('is_legal_entity', false);
    }
}
