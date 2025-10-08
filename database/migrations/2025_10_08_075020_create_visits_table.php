<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key to Patient
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            
            // Visit Tracking
            $table->string('visit_token', 5)->unique()->comment('Short token for queue management and billing');
            $table->enum('visit_type', ['Outpatient', 'Inpatient', 'Emergency'])->default('Outpatient');
            
            // Workflow Status - CRITICAL for Dashboard visualization
            $table->enum('status', [
                'Registered', 
                'Triage', 
                'Consultation', 
                'Lab/Rad', 
                'Pharmacy', 
                'Billing', 
                'Completed'
            ])->default('Registered')->index();
            
            $table->dateTime('registration_date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
