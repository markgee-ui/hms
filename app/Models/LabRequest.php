<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'visit_id',
        'doctor_id',
        'tests_requested',
        'results',
        'status',
        'lab_tech_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tests_requested' => 'array', // Cast tests requested to a usable array
    ];

    /**
     * Get the visit associated with the lab request.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Get the doctor who ordered the lab request.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the lab technician who processed the request.
     */
    public function labTech(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lab_tech_id');
    }
}
