<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the ENUM column safely
        DB::statement("ALTER TABLE visits MODIFY COLUMN status ENUM(
            'Registered',
            'Waiting for Triage',
            'Triage Completed',
            'Consultation',
            'Lab/Rad',
            'Lab/Rad Results Ready',
            'Pharmacy',
            'Inpatient',
            'Billing',
            'Completed'
        ) NOT NULL DEFAULT 'Registered'");
    }

    public function down(): void
    {
        // Rollback: remove 'Lab/Rad Results Ready' if migration is reverted
        DB::statement("ALTER TABLE visits MODIFY COLUMN status ENUM(
            'Registered',
            'Triage Completed',
            'Consultation',
            'Lab/Rad',
            'Pharmacy',
            'Completed',
            'Billing'
        ) NOT NULL DEFAULT 'Registered'");
    }
};
