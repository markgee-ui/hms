<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2', // Cast price to a decimal with 2 places
    ];
    
    // Optional: Define a relationship back to Consultation or PrescriptionItem
    // public function prescriptionItems()
    // {
    //     return $this->hasMany(PrescriptionItem::class);
    // }
}