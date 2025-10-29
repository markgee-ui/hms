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
        Schema::create('lab_request_tests', function (Blueprint $table) {
            $table->id();
            // Foreign keys
            $table->foreignId('lab_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('lab_test_id')->constrained()->onDelete('restrict'); // Links to the LabTest Catalog

            // Transactional/Result fields
            $table->text('result')->nullable(); // Placeholder for the actual test result
            $table->string('status')->default('Requested'); // e.g., Requested, Completed, Pending
            $table->timestamp('requested_at')->useCurrent();
            $table->foreignId('lab_tech_id')->nullable()->constrained('users'); // Technician who processed it

            $table->timestamps();
        });
        
        // Optional: Run this if you want to remove the old free-text/array column
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->dropColumn('tests_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the old column if you need to roll back
        Schema::table('lab_requests', function (Blueprint $table) {
            $table->json('tests_requested')->nullable();
        });
        
        Schema::dropIfExists('lab_request_tests');
    }
};