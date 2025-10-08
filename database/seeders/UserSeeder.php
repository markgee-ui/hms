<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@hms.com',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Receptionist Mark',
                'email' => 'receptionist@hms.com',
                'role' => 'receptionist',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Nurse Mary',
                'email' => 'nurse@hms.com',
                'role' => 'nurse',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Dr. Alex Ngugi',
                'email' => 'doctor@hms.com',
                'role' => 'doctor',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Pharmacist John',
                'email' => 'pharmacist@hms.com',
                'role' => 'pharmacist',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Cashier Mark',
                'email' => 'cashier@hms.com',
                'role' => 'cashier',
                'password' => Hash::make('password'),
            ],
        ];

        // Ensure we use the current timestamps
        $now = Carbon::now();
        $users = array_map(function ($user) use ($now) {
            $user['created_at'] = $now;
            $user['updated_at'] = $now;
            return $user;
        }, $users);

        // Insert the users into the database
        DB::table('users')->insert($users);
    }
}
