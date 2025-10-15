<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('visits', function (Blueprint $table) {
        // Assuming doctor_id references the 'users' table
        $table->foreignId('doctor_id')->nullable()->after('patient_id')->constrained('users'); 
    });
}

public function down()
{
    Schema::table('visits', function (Blueprint $table) {
        $table->dropForeign(['doctor_id']);
        $table->dropColumn('doctor_id');
    });
}
};
