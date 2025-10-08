<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // --- STEP 1: Temporarily add new ENUM values while preserving old ones ---
        Schema::table('visits', function (Blueprint $table) {
            $table->enum('status', [
                'Registered', 
                'Waiting for Triage', // New: Patient is in queue waiting for Nurse
                'Triage Completed',   // New: Vitals taken, ready for Doctor
                'Triage',             // Existing value kept temporarily
                'Consultation', 
                'Lab/Rad', 
                'Pharmacy', 
                'Billing', 
                'Completed'
            ])->default('Registered')->change();
        });

        // --- STEP 2: Update data using the newly available value ---
        // Change existing 'Triage' status records to the new 'Triage Completed' status
        DB::table('visits')
            ->where('status', 'Triage')
            ->update(['status' => 'Triage Completed']);
            
        // --- STEP 3: Finalize the ENUM list, removing the obsolete 'Triage' value ---
        Schema::table('visits', function (Blueprint $table) {
            $table->enum('status', [
                'Registered', 
                'Waiting for Triage', // Patient is in queue waiting for Nurse
                'Triage Completed',   // Vitals taken, ready for Doctor
                'Consultation', 
                'Lab/Rad', 
                'Pharmacy', 
                'Billing', 
                'Completed'
            ])->default('Registered')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the ENUM back to the original set and map 'Triage Completed' back to 'Triage'
        Schema::table('visits', function (Blueprint $table) {
            $table->enum('status', [
                'Registered',
                'Triage', // Original value
                'Consultation',
                'Lab/Rad',
                'Pharmacy',
                'Billing',
                'Completed'
            ])->default('Registered')->change();
        });

        // Map the new statuses back to the old one if needed (requires careful data handling)
        DB::table('visits')
            ->whereIn('status', ['Waiting for Triage', 'Triage Completed'])
            ->update(['status' => 'Triage']);
    }
};
