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
        Schema::table('users', function (Blueprint $table) {
            // Add the 'role' column. 
            // It uses 'string' type to store the role name (e.g., 'receptionist', 'doctor').
            // We set a default of 'receptionist' for initial system entry, 
            // but this should be adjusted on user creation.
            $table->string('role')->after('password')->default('receptionist')
                  ->comment('User role: admin, receptionist, nurse, doctor, pharmacist, cashier, labtech');
            
            // Adding an index for faster query performance when filtering by role
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the index first, then the column
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
