<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Nurse account
        User::create([
            'first_name' => 'Clinic',
            'last_name' => 'Nurse',
            'email' => 'nurse@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'nurse',
            'must_change_password' => false,
        ]);

        // BSIT Dean account
        User::create([
            'first_name' => 'BSIT',
            'last_name' => 'Dean',
            'email' => 'dean.bsit@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'dean',
            'department' => 'BSIT',
            'must_change_password' => false,
        ]);

        // BSBA Dean account
        User::create([
            'first_name' => 'BSBA',
            'last_name' => 'Dean',
            'email' => 'dean.bsba@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'dean',
            'department' => 'BSBA',
            'must_change_password' => false,
        ]);

        // EDUC Dean account
        User::create([
            'first_name' => 'EDUC',
            'last_name' => 'Dean',
            'email' => 'dean.educ@clinic.com',
            'password' => Hash::make('password'),
            'role' => 'dean',
            'department' => 'EDUC',
            'must_change_password' => false,
        ]);

        // Sample BSIT student
        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.bsit@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => '2024-1001',
            'course' => 'BSIT',
            'year_level' => '2nd year',
            'section' => 'A',
            'academic_year' => 2024,
            'must_change_password' => true,
        ]);

        // Sample BSBA-MM student
        User::create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'email' => 'alice.bsba@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => '2024-2001',
            'course' => 'BSBA-MM',
            'year_level' => '3rd year',
            'section' => 'B',
            'academic_year' => 2024,
            'must_change_password' => true,
        ]);

        // Sample BSBA-FM student
        User::create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob.bsba@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => '2024-2002',
            'course' => 'BSBA-FM',
            'year_level' => '1st year',
            'section' => 'A',
            'academic_year' => 2024,
            'must_change_password' => true,
        ]);

        // Sample BSED student
        User::create([
            'first_name' => 'Carol',
            'last_name' => 'Davis',
            'email' => 'carol.bsed@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => '2024-3001',
            'course' => 'BSED',
            'year_level' => '4th year',
            'section' => 'C',
            'academic_year' => 2024,
            'must_change_password' => true,
        ]);

        // Sample BEED student
        User::create([
            'first_name' => 'Diana',
            'last_name' => 'Wilson',
            'email' => 'diana.beed@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'student_id' => '2024-3002',
            'course' => 'BEED',
            'year_level' => '2nd year',
            'section' => 'A',
            'academic_year' => 2024,
            'must_change_password' => true,
        ]);
    }
}