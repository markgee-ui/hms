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
        Schema::create('lab_requests', function (Blueprint $table) {
            $table->id();
            
            // Link to the specific patient visit
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            
            // Link to the requesting doctor
            $table->foreignId('doctor_id')->constrained('users')->onDelete('restrict');
            
            // Store the requested tests (e.g., as a JSON array or comma-separated list)
            $table->json('tests_requested')->nullable()->comment('JSON array of requested test codes/names');
            
            // Field for lab technician to enter results (can be text or a path to a file/JSON)
            $table->longText('results')->nullable();

            // Status of the request: Pending, In Progress, Completed
            $table->enum('status', ['Pending', 'In Progress', 'Completed'])->default('Pending');
            
            // Link to the lab technician who processed the request (optional)
            $table->foreignId('lab_tech_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            
            // Ensure only one lab request per visit at a time (if needed, but usually one is enough)
            $table->unique('visit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_requests');
    }
};
