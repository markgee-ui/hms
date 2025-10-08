<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'visit_id',
        'nurse_id',
        'bp', // Blood Pressure
        'temperature',
        'pulse',
        'weight',
        'chief_complaint',
        'symptoms',
    ];

    /**
     * Triage belongs to a single Visit.
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Triage was recorded by a Nurse (User).
     */
    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }
}
