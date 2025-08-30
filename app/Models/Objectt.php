<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objectt extends Model
{
    protected $table = 'objects';

    protected $fillable = [
        'subject_id',
        'application_number',
        'application_date',
        'permit_document_name',
        'permit_type_id',
        'issuing_authority_id',
        'permit_date',
        'permit_number',
        'district_id',
        'address',
        'cadastre_number',
        'work_type',
        'construction_volume',
        'above_permit_volume',
        'parking_volume',
        'technical_rooms_volume',
        'common_area_volume',
        'construction_type_id',
        'object_type_id',
        'territorial_zone_id',
        'location_type',
        'additional_info',
        'geolocation',
        'is_active'
    ];

    protected $casts = [
        'application_date' => 'date',
        'permit_date' => 'date',
        'construction_volume' => 'decimal:2',
        'above_permit_volume' => 'decimal:2',
        'parking_volume' => 'decimal:2',
        'technical_rooms_volume' => 'decimal:2',
        'common_area_volume' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function constructionType()
    {
        return $this->belongsTo(ConstructionType::class, 'construction_type_id');
    }

    public function objectType()
    {
        return $this->belongsTo(ObjectType::class, 'object_type_id');
    }

    public function territorialZone()
    {
        return $this->belongsTo(TerritorialZone::class, 'territorial_zone_id');
    }

    public function permitType()
    {
        return $this->belongsTo(PermitType::class, 'permit_type_id');
    }

    public function issuingAuthority()
    {
        return $this->belongsTo(IssuingAuthority::class, 'issuing_authority_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'object_id');
    }

    // Calculate effective construction volume using formula
    public function getCalculatedVolumeAttribute(): float
    {
        return ($this->construction_volume + $this->above_permit_volume) -
               ($this->parking_volume + $this->technical_rooms_volume + $this->common_area_volume);
    }

    // Get coordinates as array
    public function getCoordinatesAttribute(): ?array
    {
        if (!$this->geolocation) {
            return null;
        }

        $coords = explode(',', $this->geolocation);
        if (count($coords) !== 2) {
            return null;
        }

        return [
            'lat' => floatval(trim($coords[0])),
            'lng' => floatval(trim($coords[1]))
        ];
    }
}
