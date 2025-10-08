<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * These fields are typically handled during registration.
     * National ID and Phone are indexed for quick searching.
     */
    protected $fillable = [
        'patient_id',
        'national_id',
        'name',
        'age',
        'gender',
        'address',
        'phone',
        'next_of_kin',
    ];

    /**
     * Define the relationship: A Patient can have many Visits.
     */
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Ensure Patient ID is accessible but national ID is kept protected.
     */
    protected $hidden = [
        'national_id',
    ];
}
