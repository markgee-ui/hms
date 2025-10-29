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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            // Foreign keys
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->foreignId('medication_id')->constrained()->onDelete('restrict'); // Links to the Medication Catalog

            // Prescription details (from doctor's form)
            $table->integer('quantity'); // Number of units (e.g., 14 tablets)
            $table->string('dosage')->nullable(); // e.g., 500mg
            $table->string('frequency')->nullable(); // e.g., Twice Daily (BD)
            $table->string('duration')->nullable(); // e.g., 7 days

            $table->timestamps();
        });

        // Optional: Run this if you want to remove the old free-text column
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('prescription_details'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the old column if you need to roll back
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->text('prescription_details')->nullable(); 
        });
        
        Schema::dropIfExists('prescription_items');
    }
};