<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INSTRUCTORS = [
        'Karam',
        'Usman',
        'Adeel Javaid',
        'Mudasar',
        'Usama',
        'Alisba',
        'Ayesha',
        'Haram',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('batches') || !Schema::hasTable('programs') || !Schema::hasTable('campuses')) {
            return;
        }

        $this->makeBatchNameNullable();
        $this->dropLegacyCodeUniqueness();
        $this->ensureCodeIndex();

        $rows = $this->parseLegacyDump();
        $this->ensureReferencedProgramsAndCampusesExist($rows);

        DB::transaction(function () use ($rows): void {
            // Use delete, not truncate, so existing FK rules on child tables are honored.
            DB::table('batches')->delete();

            foreach ($rows as $row) {
                DB::table('batches')->insert([
                    'id' => $row['id'],
                    'program_id' => $row['program_id'],
                    'campus_id' => $row['campus_id'],
                    'name' => null,
                    'code' => $row['code'],
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'session' => $row['session'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'instructor' => $this->resolveInstructor($row),
                    'lab' => $row['lab'],
                    'remarks' => $row['remarks'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]);
            }
        });
    }

    public function down(): void
    {
        // Irreversible data reload migration.
    }

    private function makeBatchNameNullable(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    private function dropLegacyCodeUniqueness(): void
    {
        if ($this->hasIndex('batches', 'batches_code_unique')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->dropUnique('batches_code_unique');
            });
        }
    }

    private function ensureCodeIndex(): void
    {
        if ($this->hasIndex('batches', 'batches_code_index')) {
            return;
        }

        Schema::table('batches', function (Blueprint $table) {
            $table->index('code');
        });
    }

    /**
     * @return list<array{
     *     id:int,
     *     program_id:int,
     *     campus_id:int,
     *     code:string,
     *     start_date:?string,
     *     end_date:?string,
     *     session:string,
     *     start_time:?string,
     *     end_time:?string,
     *     lab:?string,
     *     remarks:?string,
     *     status:string,
     *     created_at:?string,
     *     updated_at:?string
     * }>
     */
    private function parseLegacyDump(): array
    {
        $path = database_path('seeders/data/legacy_batches_2026_06_27_dump.sql');

        if (!is_file($path)) {
            throw new RuntimeException('Legacy batch dump file is missing: ' . $path);
        }

        $rows = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);

            if ($line === '' || !str_starts_with($line, '(')) {
                continue;
            }

            $columns = str_getcsv(trim($line, " \t\n\r\0\x0B(),;"), ',', "'", '\\');

            if (count($columns) !== 16) {
                throw new RuntimeException('Invalid batch dump row: ' . $line);
            }

            $rows[] = [
                'id' => (int) $columns[0],
                'program_id' => (int) $columns[2],
                'campus_id' => (int) $columns[3],
                'code' => trim((string) ($this->nullableValue($columns[5]) ?? 'BATCH-' . (int) $columns[0])),
                'start_date' => $this->nullableValue($columns[6]),
                'end_date' => $this->nullableValue($columns[7]),
                'session' => $this->normalizeSession((string) ($this->nullableValue($columns[8]) ?? 'Morning')),
                'start_time' => $this->nullableValue($columns[9]),
                'end_time' => $this->nullableValue($columns[10]),
                'lab' => $this->nullableValue($columns[11]),
                'remarks' => $this->nullableValue($columns[12]),
                'status' => $this->normalizeStatus((string) ($this->nullableValue($columns[13]) ?? 'Not Suspended')),
                'created_at' => $this->nullableValue($columns[14]),
                'updated_at' => $this->nullableValue($columns[15]),
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{program_id:int,campus_id:int}>  $rows
     */
    private function ensureReferencedProgramsAndCampusesExist(array $rows): void
    {
        $programIds = collect($rows)
            ->pluck('program_id')
            ->unique()
            ->sort()
            ->values();

        $campusIds = collect($rows)
            ->pluck('campus_id')
            ->unique()
            ->sort()
            ->values();

        $existingProgramIds = DB::table('programs')
            ->whereIn('id', $programIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $existingCampusIds = DB::table('campuses')
            ->whereIn('id', $campusIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $missingPrograms = $programIds->diff($existingProgramIds)->values()->all();
        $missingCampuses = $campusIds->diff($existingCampusIds)->values()->all();

        if ($missingPrograms !== [] || $missingCampuses !== []) {
            $parts = [];

            if ($missingPrograms !== []) {
                $parts[] = 'missing program ids: ' . implode(', ', $missingPrograms);
            }

            if ($missingCampuses !== []) {
                $parts[] = 'missing campus ids: ' . implode(', ', $missingCampuses);
            }

            throw new RuntimeException('Legacy batch import aborted because referenced records do not exist in the current database: ' . implode('; ', $parts));
        }
    }

    /**
     * @param  array{id:int,program_id:int,campus_id:int,code:string}  $row
     */
    private function resolveInstructor(array $row): string
    {
        $seed = $row['id'] . '|' . $row['program_id'] . '|' . $row['campus_id'] . '|' . $row['code'];
        $index = abs(crc32($seed)) % count(self::INSTRUCTORS);

        return self::INSTRUCTORS[$index];
    }

    private function normalizeSession(string $session): string
    {
        return match (strtolower(trim($session))) {
            'morning' => 'morning',
            'evening' => 'evening',
            'weekend' => 'weekend',
            default => throw new RuntimeException('Invalid batch session value: ' . $session),
        };
    }

    private function normalizeStatus(string $status): string
    {
        return match (strtolower(trim($status))) {
            'not suspended' => 'active',
            'suspended' => 'inactive',
            default => throw new RuntimeException('Invalid batch status value: ' . $status),
        };
    }

    private function nullableValue(string $value): ?string
    {
        $value = trim($value);

        return strtoupper($value) === 'NULL' ? null : $value;
    }

    private function hasIndex(string $table, string $index): bool
    {
        $driver = DB::getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $index)
                ->exists(),
            default => false,
        };
    }
};
