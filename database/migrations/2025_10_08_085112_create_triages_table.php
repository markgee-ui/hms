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
        Schema::create('triages', function (Blueprint $table) {
            $table->id();
            // Foreign Keys
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('nurse_id')->constrained('users')->onDelete('restrict');

            // Vital Signs
            $table->string('bp', 15)->nullable()->comment('Blood Pressure (e.g., 120/80)');
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('pulse')->nullable();
            $table->decimal('weight', 5, 2)->nullable();

            // Preliminary Assessment
            $table->string('chief_complaint', 255);
            $table->text('symptoms')->nullable();

            $table->timestamps();
            
            // Ensure only one triage record per visit
            $table->unique('visit_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triages');
    }
};
