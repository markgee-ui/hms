<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabRequestTest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'lab_request_id',
        'lab_test_id', // Link to the LabTest Catalog
        'result',
        'status', // e.g., 'Requested', 'Sampled', 'Completed'
        'requested_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'requested_at' => 'datetime',
    ];

    /**
     * Relationship: This test belongs to a specific lab request.
     */
    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    /**
     * Relationship: This test is a specific item from the catalog.
     */
    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }
}