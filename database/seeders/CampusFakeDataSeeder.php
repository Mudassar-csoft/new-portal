<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CampusFakeDataSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('users')->update(['campus_id' => null]);

        $tablesToWipe = [
            'admissions',
            'fee_collections',
            'registrations',
            'lead_followups',
            'lead_transfers',
            'leads',
            'web_leads',
            'batch_timetables',
            'student_attendances',
            'batches',
            'program_campus_discounts',
            'inventory_items',
            'campuses',
        ];

        foreach ($tablesToWipe as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
            }
        }

        Schema::enableForeignKeyConstraints();

        $rows = [
            [
                'name' => 'Career Institute Faisalabad',
                'title' => 'Faisalabad Main Campus',
                'city' => 'Faisalabad',
                'city_abbr' => 'FSD',
                'country' => 'Pakistan',
                'campus_type' => 'company',
                'campus_email' => 'fsd@career.edu.pk',
                'landline' => '041-1234567',
                'mobile' => '0300-1234567',
                'address' => 'D Ground, Peoples Colony, Faisalabad',
                'labs_count' => 6,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => 'Head office and main training centre.',
            ],
            [
                'name' => 'Career Institute Lahore Gulberg',
                'title' => 'Lahore Gulberg Branch',
                'city' => 'Lahore',
                'city_abbr' => 'LHR',
                'country' => 'Pakistan',
                'campus_type' => 'company',
                'campus_email' => 'lhr@career.edu.pk',
                'landline' => '042-7654321',
                'mobile' => '0301-7654321',
                'address' => 'MM Alam Road, Gulberg III, Lahore',
                'labs_count' => 4,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => null,
            ],
            [
                'name' => 'Career Institute Karachi Clifton',
                'title' => 'Karachi Clifton Franchise',
                'city' => 'Karachi',
                'city_abbr' => 'KHI',
                'country' => 'Pakistan',
                'campus_type' => 'franchise',
                'campus_email' => 'khi@career.edu.pk',
                'landline' => '021-2222333',
                'mobile' => '0302-2222333',
                'address' => 'Block 2, Clifton, Karachi',
                'labs_count' => 3,
                'royalty_rate' => 10.00,
                'status' => 'active',
                'remarks' => 'Franchise partner since 2024.',
            ],
            [
                'name' => 'Career Institute Islamabad',
                'title' => 'Islamabad F-7 Branch',
                'city' => 'Islamabad',
                'city_abbr' => 'ISB',
                'country' => 'Pakistan',
                'campus_type' => 'company',
                'campus_email' => 'isb@career.edu.pk',
                'landline' => '051-1112233',
                'mobile' => '0303-1112233',
                'address' => 'Jinnah Super Market, F-7, Islamabad',
                'labs_count' => 5,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => null,
            ],
            [
                'name' => 'Career Institute Multan',
                'title' => 'Multan Cantt Franchise',
                'city' => 'Multan',
                'city_abbr' => 'MUL',
                'country' => 'Pakistan',
                'campus_type' => 'franchise',
                'campus_email' => 'mul@career.edu.pk',
                'landline' => '061-9988776',
                'mobile' => '0304-9988776',
                'address' => 'Bosan Road, Cantt, Multan',
                'labs_count' => 2,
                'royalty_rate' => 8.00,
                'status' => 'inactive',
                'remarks' => 'Suspended pending renewal.',
            ],
        ];

        foreach ($rows as $row) {
            $cityAbbr = Str::upper(preg_replace('/[^A-Za-z]/', '', $row['city_abbr']));
            $count = Campus::query()->where('city_abbr', $cityAbbr)->count();
            $code = 'CI' . $cityAbbr . str_pad((string) ($count + 1), 2, '0', STR_PAD_LEFT);
            $slug = $this->uniqueSlug($row['name'] . '-' . $cityAbbr);

            Campus::create([
                'name' => $row['name'],
                'title' => $row['title'],
                'slug' => $slug,
                'code' => $code,
                'country' => $row['country'],
                'city' => $row['city'],
                'city_abbr' => $cityAbbr,
                'campus_type' => $row['campus_type'],
                'campus_email' => $row['campus_email'],
                'landline' => $row['landline'],
                'mobile' => $row['mobile'],
                'address' => $row['address'],
                'labs_count' => $row['labs_count'],
                'royalty_rate' => $row['campus_type'] === 'franchise' ? $row['royalty_rate'] : null,
                'status' => $row['status'],
                'remarks' => $row['remarks'],
            ]);
        }
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $counter = 2;

        while (Campus::query()->where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
