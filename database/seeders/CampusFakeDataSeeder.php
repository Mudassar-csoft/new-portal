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
            [
                'name' => 'Career Institute Gujranwala',
                'title' => 'Gujranwala Satellite Branch',
                'city' => 'Gujranwala',
                'city_abbr' => 'GJW',
                'country' => 'Pakistan',
                'campus_type' => 'company',
                'campus_email' => 'gjw@career.edu.pk',
                'landline' => '055-4455667',
                'mobile' => '0305-4455667',
                'address' => 'Satellite Town, Gujranwala',
                'labs_count' => 4,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => 'Regional company-owned branch.',
            ],
            [
                'name' => 'Career Institute Rawalpindi',
                'title' => 'Rawalpindi Saddar Branch',
                'city' => 'Rawalpindi',
                'city_abbr' => 'RWP',
                'country' => 'Pakistan',
                'campus_type' => 'company',
                'campus_email' => 'rwp@career.edu.pk',
                'landline' => '051-6677889',
                'mobile' => '0306-6677889',
                'address' => 'Bank Road, Saddar, Rawalpindi',
                'labs_count' => 3,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => 'Serves the twin cities region.',
            ],
            [
                'name' => 'Career Institute Peshawar',
                'title' => 'Peshawar University Road Franchise',
                'city' => 'Peshawar',
                'city_abbr' => 'PSW',
                'country' => 'Pakistan',
                'campus_type' => 'franchise',
                'campus_email' => 'psw@career.edu.pk',
                'landline' => '091-5566778',
                'mobile' => '0307-5566778',
                'address' => 'University Road, Peshawar',
                'labs_count' => 2,
                'royalty_rate' => 12.00,
                'status' => 'active',
                'remarks' => 'Franchise partner focused on short courses.',
            ],
            [
                'name' => 'Career Institute Hyderabad',
                'title' => 'Hyderabad AutoBahn Branch',
                'city' => 'Hyderabad',
                'city_abbr' => 'HYD',
                'country' => 'Pakistan',
                'campus_type' => 'company',
                'campus_email' => 'hyd@career.edu.pk',
                'landline' => '022-3344556',
                'mobile' => '0308-3344556',
                'address' => 'AutoBahn Road, Hyderabad',
                'labs_count' => 3,
                'royalty_rate' => null,
                'status' => 'active',
                'remarks' => 'Expansion campus for interior Sindh.',
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
