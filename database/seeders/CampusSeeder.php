<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $campuses = [
            [
                'name' => 'Career Institute Lahore',
                'title' => 'Career Institute Lahore',
                'slug' => 'career-institute-lahore',
                'code' => 'CILHR01',
                'country' => 'Pakistan',
                'city' => 'Lahore',
                'city_abbr' => 'LHR',
                'campus_type' => 'company',
                'campus_email' => 'lahore@career.test',
                'landline' => '042-3000-1001',
                'mobile' => '0300-0001001',
                'address' => 'Johar Town, Lahore',
                'labs_count' => 5,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => 'Demo company campus',
            ],
            [
                'name' => 'Career Institute Faisalabad',
                'title' => 'Career Institute Faisalabad',
                'slug' => 'career-institute-faisalabad',
                'code' => 'CIFSD01',
                'country' => 'Pakistan',
                'city' => 'Faisalabad',
                'city_abbr' => 'FSD',
                'campus_type' => 'company',
                'campus_email' => 'faisalabad@career.test',
                'landline' => '041-3000-1002',
                'mobile' => '0300-0001002',
                'address' => 'D Ground, Faisalabad',
                'labs_count' => 4,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => 'Demo company campus',
            ],
            [
                'name' => 'Career Franchise Gujranwala',
                'title' => 'Career Franchise Gujranwala',
                'slug' => 'career-franchise-gujranwala',
                'code' => 'CIGJW01',
                'country' => 'Pakistan',
                'city' => 'Gujranwala',
                'city_abbr' => 'GJW',
                'campus_type' => 'franchise',
                'campus_email' => 'gujranwala.franchise@career.test',
                'landline' => '055-3000-2001',
                'mobile' => '0301-0002001',
                'address' => 'Satellite Town, Gujranwala',
                'labs_count' => 3,
                'royalty_rate' => 20.00,
                'status' => 'active',
                'remarks' => 'Demo franchise with 20% royalty',
            ],
            [
                'name' => 'Career Franchise Sialkot',
                'title' => 'Career Franchise Sialkot',
                'slug' => 'career-franchise-sialkot',
                'code' => 'CISKT01',
                'country' => 'Pakistan',
                'city' => 'Sialkot',
                'city_abbr' => 'SKT',
                'campus_type' => 'franchise',
                'campus_email' => 'sialkot.franchise@career.test',
                'landline' => '052-3000-2002',
                'mobile' => '0301-0002002',
                'address' => 'Cantt Area, Sialkot',
                'labs_count' => 2,
                'royalty_rate' => 20.00,
                'status' => 'active',
                'remarks' => 'Demo franchise with 20% royalty',
            ],
        ];

        foreach ($campuses as $campus) {
            Campus::updateOrCreate(
                ['code' => $campus['code']],
                array_merge(
                    $campus,
                    ['slug' => $campus['slug'] ?: Str::slug($campus['name'])]
                )
            );
        }
    }
}
