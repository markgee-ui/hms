<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'visit_id',
        'doctor_id',
        'diagnosis',
        'notes',
        'treatment_plan',
        'status', // e.g., 'Completed', 'Ongoing'
    ];

    /**
     * A Consultation belongs to a single Visit.
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * A Consultation was performed by a Doctor (User).
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
