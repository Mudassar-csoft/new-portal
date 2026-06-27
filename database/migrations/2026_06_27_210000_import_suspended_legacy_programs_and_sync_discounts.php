<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('programs') || !Schema::hasTable('program_campus_discounts')) {
            return;
        }

        $usedCodes = [];
        $importedIds = [];

        DB::transaction(function () use (&$usedCodes, &$importedIds): void {
            foreach ($this->programRows() as $row) {
                $code = $this->resolveCode($row, $usedCodes);

                $payload = [
                    'id' => $row['id'],
                    'name' => $row['title'],
                    'title' => $row['title'],
                    'code' => $code,
                    'description' => null,
                    'program_type' => $this->normalizeProgramType($row['program_type']),
                    'fee' => $row['fee'],
                    'duration_weeks' => $row['duration_weeks'],
                    'discount_limit' => 20.00,
                    'installments' => 1,
                    'outline_path' => $row['outline_path'],
                    'prerequisite' => $row['prerequisite'],
                    'remarks' => $row['remarks'],
                    'status' => 'inactive',
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ];

                $existingById = DB::table('programs')
                    ->where('id', $row['id'])
                    ->first(['id', 'code', 'title', 'name']);

                if ($existingById && $this->sameProgram($existingById, $row['title'])) {
                    DB::table('programs')
                        ->where('id', $row['id'])
                        ->update($this->withoutId($payload));

                    $importedIds[] = (int) $row['id'];
                    continue;
                }

                $existingByCode = DB::table('programs')
                    ->where('code', $code)
                    ->first(['id', 'code', 'title', 'name']);

                if ($existingByCode && $this->sameProgram($existingByCode, $row['title'])) {
                    DB::table('programs')
                        ->where('id', $existingByCode->id)
                        ->update($this->withoutId($payload));

                    $importedIds[] = (int) $existingByCode->id;
                    continue;
                }

                if ($existingById) {
                    DB::table('programs')->insert($this->withoutId($payload));

                    $insertedId = (int) DB::table('programs')
                        ->where('code', $code)
                        ->value('id');

                    $importedIds[] = $insertedId;
                    continue;
                }

                DB::table('programs')->insert($payload);
                $importedIds[] = (int) $row['id'];
            }

            $this->syncGlobalTwentyPercentDiscounts();
        });

        echo sprintf(
            'Imported or updated %d suspended legacy program(s) and synced 20%% global discounts for all programs.',
            count(array_unique($importedIds))
        ) . PHP_EOL;
    }

    public function down(): void
    {
        // Irreversible data migration.
    }

    /**
     * @return list<array{
     *     id:int,
     *     title:string,
     *     program_type:string,
     *     base_code:string,
     *     fee:float,
     *     duration_weeks:int,
     *     outline_path:?string,
     *     prerequisite:?string,
     *     remarks:?string,
     *     created_at:?string,
     *     updated_at:?string
     * }>
     */
    private function programRows(): array
    {
        $path = database_path('seeders/data/legacy_programs_suspended_2026_06_27_dump.sql');

        if (!is_file($path)) {
            throw new RuntimeException('Legacy suspended program dump file is missing: ' . $path);
        }

        $rows = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);

            if ($line === '' || !str_starts_with($line, '(')) {
                continue;
            }

            $columns = str_getcsv(trim($line, " \t\n\r\0\x0B(),;"), ',', "'", '\\');

            if (count($columns) !== 17) {
                throw new RuntimeException('Invalid suspended program dump row: ' . $line);
            }

            $status = $this->nullableValue($columns[13]);
            $deletedAt = $this->nullableValue($columns[16]);

            if (!is_string($status) || Str::lower($status) !== 'suspended' || $deletedAt !== null) {
                continue;
            }

            $baseCode = $this->firstCode([
                $this->nullableValue($columns[4]),
                $this->nullableValue($columns[5]),
                $this->nullableValue($columns[6]),
            ]);

            $rows[] = [
                'id' => (int) $columns[0],
                'program_type' => (string) ($this->nullableValue($columns[2]) ?? 'short course'),
                'title' => (string) ($this->nullableValue($columns[3]) ?? 'Untitled Program'),
                'base_code' => $baseCode ?: 'PRG-' . (int) $columns[0],
                'fee' => (float) ($this->nullableValue($columns[7]) ?? 0),
                'duration_weeks' => (int) ($this->nullableValue($columns[8]) ?? 1),
                'outline_path' => $this->nullableValue($columns[10]),
                'prerequisite' => $this->nullableValue($columns[11]),
                'remarks' => $this->nullableValue($columns[12]),
                'created_at' => $this->nullableValue($columns[14]),
                'updated_at' => $this->nullableValue($columns[15]),
            ];
        }

        return $rows;
    }

    /**
     * @param  array{
     *     id:int,
     *     title:string,
     *     base_code:string
     * }  $row
     * @param  list<string>  $usedCodes
     */
    private function resolveCode(array $row, array &$usedCodes): string
    {
        $existingById = DB::table('programs')
            ->where('id', $row['id'])
            ->first(['code', 'title', 'name']);

        if ($existingById && $this->sameProgram($existingById, $row['title'])) {
            $code = $this->normalizeCode((string) $existingById->code);
            $usedCodes[] = $code;

            return $code;
        }

        $baseCode = $this->normalizeCode($row['base_code']);
        $candidates = [$baseCode, $baseCode . '-' . $row['id']];
        $suffix = 2;

        while (true) {
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $usedCodes, true)) {
                    continue;
                }

                $existingByCode = DB::table('programs')
                    ->where('code', $candidate)
                    ->first(['id', 'code', 'title', 'name']);

                if ($existingByCode === null || $this->sameProgram($existingByCode, $row['title'])) {
                    $usedCodes[] = $candidate;

                    return $candidate;
                }
            }

            $candidates = [$baseCode . '-' . $row['id'] . '-' . $suffix];
            $suffix++;
        }
    }

    private function syncGlobalTwentyPercentDiscounts(): void
    {
        $now = now();

        DB::table('program_campus_discounts')->update([
            'discount_percent' => 20.00,
            'status' => 'active',
            'updated_at' => $now,
        ]);

        $programIds = DB::table('programs')
            ->orderBy('id')
            ->pluck('id');

        foreach ($programIds as $programId) {
            $globalRowIds = DB::table('program_campus_discounts')
                ->where('program_id', (int) $programId)
                ->whereNull('campus_id')
                ->orderBy('id')
                ->pluck('id');

            if ($globalRowIds->isEmpty()) {
                DB::table('program_campus_discounts')->insert([
                    'program_id' => (int) $programId,
                    'campus_id' => null,
                    'discount_percent' => 20.00,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                continue;
            }

            $keepId = (int) $globalRowIds->first();

            DB::table('program_campus_discounts')
                ->where('id', $keepId)
                ->update([
                    'discount_percent' => 20.00,
                    'status' => 'active',
                    'updated_at' => $now,
                ]);

            $duplicateIds = $globalRowIds
                ->skip(1)
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($duplicateIds->isNotEmpty()) {
                DB::table('program_campus_discounts')
                    ->whereIn('id', $duplicateIds->all())
                    ->delete();
            }
        }
    }

    private function normalizeProgramType(string $programType): string
    {
        return match (Str::lower(trim($programType))) {
            'short course' => 'short course',
            'diploma' => 'diploma',
            'certification' => 'certificate',
            default => Str::lower(trim($programType)),
        };
    }

    private function normalizeCode(string $code): string
    {
        $normalized = Str::upper(trim($code));
        $normalized = preg_replace('/\s+/', '-', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^A-Z0-9-]/', '', $normalized) ?? $normalized;

        return $normalized !== '' ? $normalized : 'PROGRAM';
    }

    private function nullableValue(string $value): ?string
    {
        $value = trim($value);

        return strtoupper($value) === 'NULL' ? null : $value;
    }

    /**
     * @param  array<int, string|null>  $codes
     */
    private function firstCode(array $codes): ?string
    {
        foreach ($codes as $code) {
            if ($code !== null && trim($code) !== '') {
                return $code;
            }
        }

        return null;
    }

    private function sameProgram(stdClass $program, string $title): bool
    {
        $existingTitle = trim((string) ($program->title ?? $program->name ?? ''));

        return $existingTitle !== '' && $existingTitle === trim($title);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withoutId(array $payload): array
    {
        unset($payload['id']);

        return $payload;
    }
};
