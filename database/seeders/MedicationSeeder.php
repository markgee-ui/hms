<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medications = [
            // Antibiotics
            ['name' => 'Amoxicillin 500mg Capsule', 'price' => 1.50],
            ['name' => 'Azithromycin 250mg Tablet', 'price' => 3.25],
            ['name' => 'Ciprofloxacin 500mg Tablet', 'price' => 2.00],

            // Pain Relief & Anti-inflammatory
            ['name' => 'Paracetamol 500mg Tablet (Acetaminophen)', 'price' => 0.50],
            ['name' => 'Ibuprofen 400mg Tablet', 'price' => 0.75],
            ['name' => 'Diclofenac 50mg Tablet', 'price' => 1.10],

            // Chronic & Other
            ['name' => 'Metformin 500mg Tablet (for Diabetes)', 'price' => 0.60],
            ['name' => 'Amlodipine 5mg Tablet (for Hypertension)', 'price' => 1.00],
            ['name' => 'Omeprazole 20mg Capsule (for Gastric Issues)', 'price' => 0.90],
            ['name' => 'Diphenhydramine 25mg Tablet (Antihistamine)', 'price' => 0.80],
        ];

        DB::table('medications')->insert($medications);
    }
}