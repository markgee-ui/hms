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
        // Creates the 'consultations' table
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();

            // Link to the specific patient visit. Unique constraint ensures one consultation per visit.
            $table->foreignId('visit_id')->unique()->constrained('visits')->onDelete('cascade');
            
            // Link to the doctor (user) who performed the consultation.
            $table->foreignId('doctor_id')->constrained('users')->onDelete('restrict');
            
            // Consultation details
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->text('treatment_plan')->nullable();
            
            // Consultation status (e.g., Ongoing, Completed)
            $table->string('status')->default('Ongoing')->comment('e.g., Ongoing, Completed');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
