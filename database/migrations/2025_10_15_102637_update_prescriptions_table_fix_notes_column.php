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
    Schema::table('prescriptions', function (Blueprint $table) {
        // Option A: If you don't need 'notes'
        $table->dropColumn('notes');

        // OR Option B: If you still want it but optional
        // $table->text('notes')->nullable()->change();
    });
}

public function down()
{
    Schema::table('prescriptions', function (Blueprint $table) {
        $table->text('notes')->nullable();
    });
}

};
