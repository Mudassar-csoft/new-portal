<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('campuses')) {
            return;
        }

        DB::transaction(function (): void {
            foreach ($this->legacyCampuses() as $legacy) {
                $payload = $this->transformLegacyCampus($legacy);
                $existingByCode = DB::table('campuses')
                    ->where('code', $payload['code'])
                    ->first(['id']);
                $existingById = DB::table('campuses')
                    ->where('id', $payload['id'])
                    ->first(['id']);

                if ($existingByCode !== null) {
                    DB::table('campuses')
                        ->where('id', $existingByCode->id)
                        ->update($this->withoutId($payload));

                    continue;
                }

                if ($existingById !== null) {
                    DB::table('campuses')
                        ->where('id', $payload['id'])
                        ->update($this->withoutId($payload));

                    continue;
                }

                DB::table('campuses')->insert($payload);
            }
        });
    }

    public function down(): void
    {
        // This data migration is intentionally irreversible.
    }

    /**
     * @return array<int, array<string, int|float|string|null>>
     */
    private function legacyCampuses(): array
    {
        return [
            [
                'id' => 6,
                'campus_title' => 'Career Institute - Madina Town Campus',
                'campus_code' => 'CIFSD01',
                'city' => 'Faisalabad',
                'city_abbreviation' => 'FSD',
                'campus_landline_number' => '0418542950',
                'campus_mobile_number' => '03007662050',
                'campus_email_address' => 'cifsd01@career.edu.pk',
                'campus_address' => 'Career Institute, P-49, Chenab Market, Susan Road, Block Z, Madina Town, Faisalabad, Punjab, Pakistan - 38000',
                'labs_in_campus' => 4,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'NA',
                'percentage' => 0.00,
                'created_at' => '2022-08-06 02:53:56',
                'updated_at' => '2025-04-29 10:38:02',
            ],
            [
                'id' => 7,
                'campus_title' => 'Career Institute - Jinnah Colony Campus',
                'campus_code' => 'CIFSD02',
                'city' => 'Faisalabad',
                'city_abbreviation' => 'FSD',
                'campus_landline_number' => '0412640083',
                'campus_mobile_number' => '03002032970',
                'campus_email_address' => 'cifsd02@career.edu.pk',
                'campus_address' => 'Career Institute, P-54, 3rd Floor, BC Tower, Jinnah Colony, Near GC University - Gate 6, Faisalabad, Punjab, Pakistan - 38000',
                'labs_in_campus' => 5,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'abc',
                'percentage' => 0.00,
                'created_at' => '2022-08-06 03:17:19',
                'updated_at' => '2022-10-29 06:06:16',
            ],
            [
                'id' => 8,
                'campus_title' => 'Career Institute - Millat Chowk Campus',
                'campus_code' => 'CIFSD03',
                'city' => 'Faisalabad',
                'city_abbreviation' => 'FSD',
                'campus_landline_number' => '0418580027',
                'campus_mobile_number' => '03158580027',
                'campus_email_address' => 'cifsd03@career.edu.pk',
                'campus_address' => 'Career Institute, P-165 B, 262 Millat Rd, Millat Chowk, Gulistan Colony, Faisalabad, Punjab, Pakistan - 38000',
                'labs_in_campus' => 10,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'abc',
                'percentage' => 0.00,
                'created_at' => '2022-08-06 03:19:06',
                'updated_at' => '2022-10-29 06:06:33',
            ],
            [
                'id' => 9,
                'campus_title' => 'Career Institute - Satiana Road Campus',
                'campus_code' => 'CIFSD04',
                'city' => 'Faisalabad',
                'city_abbreviation' => 'FSD',
                'campus_landline_number' => '0418580027',
                'campus_mobile_number' => '03158580027',
                'campus_email_address' => 'cifsd04@career.edu.pk',
                'campus_address' => 'Career Institute, P-703, Batala Colony, Main Satiana Road, Faisalabad, Punjab, Pakistan - 38000',
                'labs_in_campus' => 3,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'abc',
                'percentage' => 0.00,
                'created_at' => '2022-08-06 03:20:37',
                'updated_at' => '2022-10-29 06:06:49',
            ],
            [
                'id' => 10,
                'campus_title' => 'Career Institute - Samnabad Campus',
                'campus_code' => 'CIFSD05',
                'city' => 'Faisalabad',
                'city_abbreviation' => 'FSD',
                'campus_landline_number' => '0418580027',
                'campus_mobile_number' => '03158580027',
                'campus_email_address' => 'cifsd05@career.edu.pk',
                'campus_address' => 'Career Institute, P-649, Canal Link Road, Samanabad, Faisalabad, Punjab, Pakistan - 38000',
                'labs_in_campus' => 4,
                'status' => 'suspended',
                'campus_type' => 'franchise',
                'remarks' => 'abc',
                'percentage' => 0.00,
                'created_at' => '2022-08-06 03:22:37',
                'updated_at' => '2025-08-19 10:40:49',
            ],
            [
                'id' => 12,
                'campus_title' => 'Career Institute - Sahiwal Campus',
                'campus_code' => 'CISWL01',
                'city' => 'Sahiwal',
                'city_abbreviation' => 'SWL',
                'campus_landline_number' => '0404510179',
                'campus_mobile_number' => '03158580027',
                'campus_email_address' => 'ciswl01@career.edu.pk',
                'campus_address' => 'Career Institute, P-4, 1st Floor, College Chowk, Near Punjab Bank, Farid Town, Sahiwal, Punjab, Pakistan - 57000',
                'labs_in_campus' => 3,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'Demo',
                'percentage' => 0.00,
                'created_at' => '2022-10-03 04:59:11',
                'updated_at' => '2022-10-29 06:07:24',
            ],
            [
                'id' => 13,
                'campus_title' => 'Career Institute - Rahim Yar Khan Campus',
                'campus_code' => 'CIRYK01',
                'city' => 'Rahim Yar Khan',
                'city_abbreviation' => 'RYK',
                'campus_landline_number' => '0685874010',
                'campus_mobile_number' => '03144444010',
                'campus_email_address' => 'ciryk01@career.edu.pk',
                'campus_address' => 'P-22, Near DCO Office, New Officer Colony, Rahim Yar Khan, Punjab, Pakistan - 64200',
                'labs_in_campus' => 6,
                'status' => 'suspended',
                'campus_type' => 'franchise',
                'remarks' => 'Career Institute - Rahim Yar Khan Campus',
                'percentage' => 0.00,
                'created_at' => '2023-02-21 20:57:20',
                'updated_at' => '2025-08-19 10:41:59',
            ],
            [
                'id' => 14,
                'campus_title' => 'Career Institute - Sargodha Campus',
                'campus_code' => 'CISGD01',
                'city' => 'Sargodha',
                'city_abbreviation' => 'SGD',
                'campus_landline_number' => '0418580027',
                'campus_mobile_number' => '03158580027',
                'campus_email_address' => 'info@career.edu.pk',
                'campus_address' => 'Career Institute, 108 A, 1st Floor, Sherazi Tower, Zafar Ullah Road, Satellite Town, Sargodha, Punjab, Pakistan - 40100',
                'labs_in_campus' => 10,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'campus',
                'percentage' => 0.00,
                'created_at' => '2023-03-17 21:52:39',
                'updated_at' => '2023-03-17 21:52:39',
            ],
            [
                'id' => 15,
                'campus_title' => 'Career Institute - Lahore DHA Phase 8 Branch',
                'campus_code' => 'CILHR01',
                'city' => 'Lahore',
                'city_abbreviation' => 'LHR',
                'campus_landline_number' => '0418580027',
                'campus_mobile_number' => '03145000083',
                'campus_email_address' => 'cilhr01@career.edu.pk',
                'campus_address' => 'Career Institute, 6th Floor, DHA Business Hub Right Wing, Phase 8, Lahore, Punjab, Pakistan',
                'labs_in_campus' => 5,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'Lahore Branch',
                'percentage' => 0.00,
                'created_at' => '2023-09-24 04:40:09',
                'updated_at' => '2023-09-24 04:43:44',
            ],
            [
                'id' => 16,
                'campus_title' => 'Career Institute Virtual Campus',
                'campus_code' => 'CIVTL01',
                'city' => 'Lahore',
                'city_abbreviation' => 'LHR',
                'campus_landline_number' => '0418724010',
                'campus_mobile_number' => '03414444010',
                'campus_email_address' => 'cipak@career.edu.pk',
                'campus_address' => 'Virtual Campus Pakistan',
                'labs_in_campus' => 100,
                'status' => 'active',
                'campus_type' => 'owned',
                'remarks' => 'Virtual Campus',
                'percentage' => 0.00,
                'created_at' => '2024-01-11 23:59:19',
                'updated_at' => '2024-01-11 23:59:19',
            ],
            [
                'id' => 17,
                'campus_title' => 'Career Institute Okara Campus',
                'campus_code' => 'CIOKR01',
                'city' => 'Okara',
                'city_abbreviation' => 'OKR',
                'campus_landline_number' => '0442707418',
                'campus_mobile_number' => '03450031418',
                'campus_email_address' => 'ciokr01@career.edu.pk',
                'campus_address' => 'Career Institute, Ellahi Baksh Pharmacy Building, 2nd Floor, D Block, Zia Ud Din Chowk, Tehsil Road, Okara, Punjab, Pakistan',
                'labs_in_campus' => 5,
                'status' => 'suspended',
                'campus_type' => 'franchise',
                'remarks' => 'NA',
                'percentage' => 0.00,
                'created_at' => '2024-04-26 22:27:11',
                'updated_at' => '2025-08-19 10:41:26',
            ],
            [
                'id' => 19,
                'campus_title' => 'Career Institute - Wapda Town Branch',
                'campus_code' => 'CILHR02',
                'city' => 'Lahore',
                'city_abbreviation' => 'LHR',
                'campus_landline_number' => '04237872166',
                'campus_mobile_number' => '03414444010',
                'campus_email_address' => 'cilhr02@career.edu.pk',
                'campus_address' => 'Building No. 268-269, Main Blvd, Block C, PIA Road, Lahore, Punjab, Pakistan - 54770',
                'labs_in_campus' => 3,
                'status' => 'active',
                'campus_type' => 'franchise',
                'remarks' => 'NA',
                'percentage' => 0.00,
                'created_at' => '2024-11-01 13:24:32',
                'updated_at' => '2024-11-01 13:24:32',
            ],
            [
                'id' => 20,
                'campus_title' => 'Career Institute - Kohinoor Branch Faisalabad',
                'campus_code' => 'CIFSD06',
                'city' => 'Faisalabad',
                'city_abbreviation' => 'FSD',
                'campus_landline_number' => '0418724010',
                'campus_mobile_number' => '03144444010',
                'campus_email_address' => 'cifsd06@career.edu.pk',
                'campus_address' => 'Kohinoor Mall 1, Abubakar Road, Opposite Legacy School, Kohinoor City, Faisalabad, Punjab, Pakistan',
                'labs_in_campus' => 24,
                'status' => 'active',
                'campus_type' => 'franchise',
                'remarks' => 'NA',
                'percentage' => 20.00,
                'created_at' => '2024-12-10 09:27:59',
                'updated_at' => '2025-08-26 13:37:14',
            ],
            [
                'id' => 21,
                'campus_title' => 'Head Office',
                'campus_code' => 'CIHO01',
                'city' => 'Faisalabad',
                'city_abbreviation' => 'HO',
                'campus_landline_number' => '0418724010',
                'campus_mobile_number' => '03144444010',
                'campus_email_address' => 'ciho01@career.edu.pk',
                'campus_address' => 'Satiyana Road.FSD',
                'labs_in_campus' => 3,
                'status' => 'active',
                'campus_type' => 'franchise',
                'remarks' => 'Done',
                'percentage' => 0.00,
                'created_at' => '2025-04-05 14:08:10',
                'updated_at' => '2025-04-05 14:08:10',
            ],
            [
                'id' => 22,
                'campus_title' => 'Career Institute - Jhang Campus',
                'campus_code' => 'CIJHG01',
                'city' => 'Jhang',
                'city_abbreviation' => 'JHG',
                'campus_landline_number' => '03145000083',
                'campus_mobile_number' => '03145000083',
                'campus_email_address' => 'cijhg01@career.edu.pk',
                'campus_address' => 'Sabri Tower Gojra Road, Jhang 35200',
                'labs_in_campus' => 3,
                'status' => 'suspended',
                'campus_type' => 'franchise',
                'remarks' => 'done',
                'percentage' => 0.00,
                'created_at' => '2025-05-05 11:33:07',
                'updated_at' => '2026-02-21 10:50:44',
            ],
        ];
    }

    /**
     * @param  array<string, int|float|string|null>  $legacy
     * @return array<string, int|float|string|null>
     */
    private function transformLegacyCampus(array $legacy): array
    {
        $campusType = strtolower(trim((string) ($legacy['campus_type'] ?? 'owned'))) === 'franchise'
            ? 'franchise'
            : 'company';
        $status = strtolower(trim((string) ($legacy['status'] ?? 'active'))) === 'suspended'
            ? 'inactive'
            : 'active';
        $name = trim((string) ($legacy['campus_title'] ?? ''));
        $code = trim((string) ($legacy['campus_code'] ?? ''));
        $percentage = round((float) ($legacy['percentage'] ?? 0), 2);

        return [
            'id' => (int) $legacy['id'],
            'name' => $name,
            'title' => $name,
            'slug' => Str::slug($name . '-' . $code),
            'code' => $code,
            'country' => 'Pakistan',
            'city' => $this->nullableString($legacy['city'] ?? null),
            'city_abbr' => $this->nullableString($legacy['city_abbreviation'] ?? null),
            'campus_type' => $campusType,
            'campus_email' => $this->nullableString($legacy['campus_email_address'] ?? null),
            'landline' => $this->nullableString($legacy['campus_landline_number'] ?? null),
            'mobile' => $this->nullableString($legacy['campus_mobile_number'] ?? null),
            'address' => $this->nullableString($legacy['campus_address'] ?? null),
            'labs_count' => (int) ($legacy['labs_in_campus'] ?? 0),
            'royalty_rate' => $campusType === 'franchise' ? $percentage : null,
            'status' => $status,
            'remarks' => $this->nullableString($legacy['remarks'] ?? null),
            'created_at' => $legacy['created_at'] ?? now(),
            'updated_at' => $legacy['updated_at'] ?? now(),
        ];
    }

    /**
     * @param  array<string, int|float|string|null>  $payload
     * @return array<string, float|int|string|null>
     */
    private function withoutId(array $payload): array
    {
        unset($payload['id']);

        return $payload;
    }

    private function nullableString(int|float|string|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
};
