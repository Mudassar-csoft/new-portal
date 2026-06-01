<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            HrmAccessSeeder::class,
            CampusSeeder::class,
            CampusUserSeeder::class,
            ProgramSeeder::class,
            DemoAcademicSeeder::class,
            FinanceSetupSeeder::class,
        ]);
    }
}
