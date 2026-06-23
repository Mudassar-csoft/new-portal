<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $campuses = [
            [
                'id' => 6,
                'name' => 'Career Institute - Madina Town Campus',
                'title' => 'Career Institute - Madina Town Campus',
                'slug' => Str::slug('Career Institute - Madina Town Campus'),
                'code' => 'CIFSD01',
                'country' => 'Pakistan',
                'city' => 'Faisalabad',
                'city_abbr' => 'FSD',
                'campus_type' => 'company',
                'campus_email' => 'cifsd01@career.edu.pk',
                'landline' => '0418542950',
                'mobile' => '03007662050',
                'address' => 'Career Institute, P-49, Chenab Market, Susan Road, Block Z, Madina Town, Faisalabad, Punjab, Pakistan - 38000',
                'labs_count' => 4,
                'royalty_rate' => 0.00,
                'status' => 'active',
                'remarks' => 'NA',
                'created_at' => '2022-08-06 02:53:56',
                'updated_at' => '2025-04-29 10:38:02',
            ],
            [
                'id' => 7,
                'name' => 'Career Institute - Jinnah Colony Campus',
                'title' => 'Career Institute - Jinnah Colony Campus',
                'slug' => Str::slug('Career Institute - Jinnah Colony Campus'),
                'code' => 'CIFSD02',
                'country' => 'Pakistan',
                'city' => 'Faisalabad',
                'city_abbr' => 'FSD',
                'campus_type' => 'company',
                'campus_email' => 'cifsd02@career.edu.pk',
                'landline' => '0412640083',
                'mobile' => '03002032970',
                'address' => 'Career Institute, P-54, 3rd Floor, BC Tower, Jinnah Colony, Near GC University - Gate 6, Faisalabad, Punjab, Pakistan - 38000',
                'labs_count' => 5,
                'royalty_rate' => 0.00,
                'status' => 'active',
                'remarks' => 'abc',
                'created_at' => '2022-08-06 03:17:19',
                'updated_at' => '2022-10-29 06:06:16',
            ],
            [
                'id' => 8,
                'name' => 'Career Institute - Millat Chowk Campus',
                'title' => 'Career Institute - Millat Chowk Campus',
                'slug' => Str::slug('Career Institute - Millat Chowk Campus'),
                'code' => 'CIFSD03',
                'country' => 'Pakistan',
                'city' => 'Faisalabad',
                'city_abbr' => 'FSD',
                'campus_type' => 'company',
                'campus_email' => 'cifsd03@career.edu.pk',
                'landline' => '0418580027',
                'mobile' => '03158580027',
                'address' => 'Career Institute, P-165 B, 262 Millat Rd, Millat Chowk, Gulistan Colony, Faisalabad, Punjab, Pakistan - 38000',
                'labs_count' => 10,
                'royalty_rate' => 0.00,
                'status' => 'active',
                'remarks' => 'abc',
                'created_at' => '2022-08-06 03:19:06',
                'updated_at' => '2022-10-29 06:06:33',
            ],
            [
                'id' => 9,
                'name' => 'Career Institute - Satiana Road Campus',
                'title' => 'Career Institute - Satiana Road Campus',
                'slug' => Str::slug('Career Institute - Satiana Road Campus'),
                'code' => 'CIFSD04',
                'country' => 'Pakistan',
                'city' => 'Faisalabad',
                'city_abbr' => 'FSD',
                'campus_type' => 'company',
                'campus_email' => 'cifsd04@career.edu.pk',
                'landline' => '0418580027',
                'mobile' => '03158580027',
                'address' => 'Career Institute, P-703, Batala Colony, Main Satiana Road, Faisalabad, Punjab, Pakistan - 38000',
                'labs_count' => 3,
                'royalty_rate' => 0.00,
                'status' => 'active',
                'remarks' => 'abc',
                'created_at' => '2022-08-06 03:20:37',
                'updated_at' => '2022-10-29 06:06:49',
            ],
        ];

        DB::table('campuses')->upsert(
            $campuses,
            ['id'],
            [
                'name',
                'title',
                'slug',
                'code',
                'country',
                'city',
                'city_abbr',
                'campus_type',
                'campus_email',
                'landline',
                'mobile',
                'address',
                'labs_count',
                'royalty_rate',
                'status',
                'remarks',
                'created_at',
                'updated_at',
            ]
        );
    }
}
