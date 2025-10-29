<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labTests = [
            // Common Hematology
            ['name' => 'Complete Blood Count (CBC)', 'price' => 50.00],
            ['name' => 'Erythrocyte Sedimentation Rate (ESR)', 'price' => 30.00],

            // Biochemistry
            ['name' => 'Fasting Blood Sugar (FBS)', 'price' => 25.00],
            ['name' => 'HbA1c', 'price' => 80.00],
            ['name' => 'Kidney Function Test (KFT / RFT)', 'price' => 75.00],
            ['name' => 'Liver Function Test (LFT)', 'price' => 90.00],

            // Microbiology & Others
            ['name' => 'Urine Routine & Microscopy (URM)', 'price' => 35.00],
            ['name' => 'Stool Analysis', 'price' => 35.00],
            ['name' => 'Widal Test', 'price' => 45.00],
            
            // Radiology Placeholder (as suggested in your code, for free-text orders)
            // If you decide to add fixed radiology items, they can go here.
            ['name' => 'X-ray Chest PA View', 'price' => 120.00],
        ];

        DB::table('lab_tests')->insert($labTests);
    }
}