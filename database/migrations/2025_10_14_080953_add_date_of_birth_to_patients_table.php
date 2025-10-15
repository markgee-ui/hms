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
        // Add the date_of_birth column to the patients table
        Schema::table('patients', function (Blueprint $table) {
            // FIX: Make the column nullable temporarily to allow existing rows to be modified.
            // If you want to enforce NOT NULL for new records later, you should run a second migration
            // to populate existing rows and then change the column.
            $table->date('date_of_birth')->after('national_id')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Define the logic to remove the column if the migration is rolled back
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });
    }
};
