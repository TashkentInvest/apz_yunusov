<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objectt extends Model
{
    use HasFactory;

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
        'geolocation'
    ];

    protected $casts = [
        'application_date' => 'date',
        'permit_date' => 'date',
        'construction_volume' => 'decimal:2',
        'above_permit_volume' => 'decimal:2',
        'parking_volume' => 'decimal:2',
        'technical_rooms_volume' => 'decimal:2',
        'common_area_volume' => 'decimal:2'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}
