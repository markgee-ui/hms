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
        Schema::table('prescriptions', function (Blueprint $table) {
            // Add the foreign key for the Pharmacist (nullable in case of partial dispensing logs)
            $table->foreignId('pharmacist_id')
                  ->nullable()
                  ->after('doctor_id') // Placing it logically after doctor_id
                  ->constrained('users'); // Assuming your user table is named 'users'

            // Add a field for the pharmacist's notes during dispensation
            $table->text('dispense_notes')->nullable()->after('prescription_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['pharmacist_id']);
            
            // Drop the columns
            $table->dropColumn('pharmacist_id');
            $table->dropColumn('dispense_notes');
        });
    }
};