<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            ['program_type' => 'certificate', 'title' => 'Microsoft Office Management', 'code' => 'MOM', 'fee' => 30000, 'duration_weeks' => 12, 'installments' => 1, 'status' => 'active'],
            ['program_type' => 'certificate', 'title' => 'WordPress Theme & Plugin Development', 'code' => 'WP', 'fee' => 35000, 'duration_weeks' => 8, 'installments' => 2, 'status' => 'active'],
            ['program_type' => 'diploma', 'title' => 'Full Stack Developer', 'code' => 'FSD', 'fee' => 120000, 'duration_weeks' => 24, 'installments' => 4, 'status' => 'active'],
            ['program_type' => 'bootcamp', 'title' => 'Data Science', 'code' => 'DS', 'fee' => 95000, 'duration_weeks' => 16, 'installments' => 3, 'status' => 'active'],
            ['program_type' => 'workshop', 'title' => 'Cyber Security Essentials', 'code' => 'CSE', 'fee' => 50000, 'duration_weeks' => 10, 'installments' => 2, 'status' => 'active'],
        ];

        foreach ($programs as $program) {
            Program::firstOrCreate(
                ['code' => $program['code']],
                array_merge(['name' => $program['title']], $program)
            );
        }
    }
}
