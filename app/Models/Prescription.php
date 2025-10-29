<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'visit_id',
        'doctor_id',
        'patient_id',

        'status',
    ];

    /**
     * Relationship: Prescription belongs to a visit.
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Relationship: Prescription belongs to a doctor (user).
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Relationship: Prescription belongs to a patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}

