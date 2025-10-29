<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'prescription_id',
        'medication_id', 
        'quantity',
        'dosage',
        'frequency',
        'duration',
    ];

    /**
     * Relationship: This item belongs to a specific prescription.
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Relationship: This item is a specific medication from the catalog.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }
}