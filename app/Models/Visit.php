<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'patient_id',
        'visit_token',
        'visit_type',
        'status',
        'registration_date',
        'doctor_id', 
    ];

    /**
     * Define the relationship: A Visit belongs to one Patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Define the relationship: A Visit has one Triage record. (Workflow Step 2)
     */
    public function triage()
    {
        return $this->hasOne(Triage::class);
    }

    /**
     * Define the relationship: A Visit has one Consultation record. (Workflow Step 3)
     */
    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }

    /**
     * Define the relationship: A Visit has one Billing record. (Workflow Step 6)
     */
    public function billing()
    {
        return $this->hasOne(Billing::class);
    }

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'registration_date' => 'datetime',
    ];
}
