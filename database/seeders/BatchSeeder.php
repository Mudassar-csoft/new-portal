<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use stdClass;

class BatchSeeder extends Seeder
{
    private const CAMPUS_IDS = [6, 7, 8, 9];

    private const INSTRUCTORS = [
        'Usman',
        'Usama',
        'Ahmad',
        'Kamran',
        'Bilal',
        'Ahsan',
    ];

    public function run(): void
    {
        $usedCodes = [];
        $campusIds = array_fill_keys(
            DB::table('campuses')->whereIn('id', self::CAMPUS_IDS)->pluck('id')->map(fn ($id) => (int) $id)->all(),
            true
        );
        $programIds = array_fill_keys(
            DB::table('programs')->pluck('id')->map(fn ($id) => (int) $id)->all(),
            true
        );

        $skippedCampusIds = [];
        $skippedProgramIds = [];

        foreach ($this->batchRows() as $row) {
            if (!isset($campusIds[$row['campus_id']])) {
                $skippedCampusIds[$row['campus_id']] = ($skippedCampusIds[$row['campus_id']] ?? 0) + 1;

                continue;
            }

            if (!isset($programIds[$row['program_id']])) {
                $skippedProgramIds[$row['program_id']] = ($skippedProgramIds[$row['program_id']] ?? 0) + 1;

                continue;
            }

            $code = $this->resolveCode($row, $usedCodes);
            $payload = [
                'id' => $row['id'],
                'program_id' => $row['program_id'],
                'campus_id' => $row['campus_id'],
                'name' => $row['name'],
                'code' => $code,
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'session' => $row['session'],
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'instructor' => $this->resolveInstructor($row['id']),
                'lab' => $row['lab'],
                'status' => 'active',
                'remarks' => $row['remarks'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
            ];

            $existingById = DB::table('batches')
                ->where('id', $row['id'])
                ->first(['id', 'code', 'name', 'program_id', 'campus_id']);

            if ($existingById && $this->sameBatch($existingById, $row)) {
                DB::table('batches')
                    ->where('id', $row['id'])
                    ->update($this->withoutId($payload));

                continue;
            }

            $existingByCode = DB::table('batches')
                ->where('code', $code)
                ->first(['id', 'code', 'name', 'program_id', 'campus_id']);

            if ($existingByCode && $this->sameBatch($existingByCode, $row)) {
                DB::table('batches')
                    ->where('id', $existingByCode->id)
                    ->update($this->withoutId($payload));

                continue;
            }

            if ($existingById) {
                DB::table('batches')->insert($this->withoutId($payload));

                $this->command?->warn(sprintf(
                    'Batch %s inserted without fixed id %d because that id is already in use.',
                    $code,
                    $row['id']
                ));

                continue;
            }

            DB::table('batches')->insert($payload);
        }

        if ($skippedCampusIds !== []) {
            $this->command?->warn('Skipped batch rows for missing campuses: ' . $this->formatSkipSummary($skippedCampusIds));
        }

        if ($skippedProgramIds !== []) {
            $this->command?->warn('Skipped batch rows for missing programs: ' . $this->formatSkipSummary($skippedProgramIds));
        }
    }

    /**
     * @return list<array{
     *     id: int,
     *     program_id: int,
     *     campus_id: int,
     *     name: string,
     *     batch_code: string,
     *     start_date: string|null,
     *     end_date: string|null,
     *     session: string,
     *     start_time: string|null,
     *     end_time: string|null,
     *     lab: string|null,
     *     remarks: string|null,
     *     created_at: string|null,
     *     updated_at: string|null
     * }>
     */
    private function batchRows(): array
    {
        $path = __DIR__ . '/data/batches_campuses_6_9_not_suspended_dump.sql';

        if (!is_file($path)) {
            throw new RuntimeException('Batch dump seed data file is missing.');
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

            $campusId = (int) $columns[3];
            $status = $this->nullableValue($columns[13]);

            if ($status !== 'Not Suspended' || !in_array($campusId, self::CAMPUS_IDS, true)) {
                continue;
            }

            $batchCode = trim((string) ($this->nullableValue($columns[5]) ?? 'BATCH-' . (int) $columns[0]));

            $rows[] = [
                'id' => (int) $columns[0],
                'program_id' => (int) $columns[2],
                'campus_id' => $campusId,
                'name' => $batchCode,
                'batch_code' => $batchCode,
                'start_date' => $this->nullableValue($columns[6]),
                'end_date' => $this->nullableValue($columns[7]),
                'session' => $this->normalizeSession((string) ($this->nullableValue($columns[8]) ?? 'Morning')),
                'start_time' => $this->nullableValue($columns[9]),
                'end_time' => $this->nullableValue($columns[10]),
                'lab' => $this->nullableValue($columns[11]),
                'remarks' => $this->nullableValue($columns[12]),
                'created_at' => $this->nullableValue($columns[14]),
                'updated_at' => $this->nullableValue($columns[15]),
            ];
        }

        return $rows;
    }

    /**
     * @param  array{
     *     id: int,
     *     program_id: int,
     *     campus_id: int,
     *     name: string,
     *     batch_code: string
     * }  $row
     * @param  list<string>  $usedCodes
     */
    private function resolveCode(array $row, array &$usedCodes): string
    {
        $existingById = DB::table('batches')
            ->where('id', $row['id'])
            ->first(['code', 'name', 'program_id', 'campus_id']);

        if ($existingById && $this->sameBatch($existingById, $row)) {
            $code = $this->normalizeCode((string) $existingById->code, $row['id']);
            $usedCodes[] = $code;

            return $code;
        }

        $baseCode = $this->normalizeCode($row['batch_code'], $row['id']);
        $candidates = [$baseCode, $baseCode . '-' . $row['id']];
        $suffix = 2;

        while (true) {
            foreach ($candidates as $candidate) {
                if (in_array($candidate, $usedCodes, true)) {
                    continue;
                }

                $existingByCode = DB::table('batches')
                    ->where('code', $candidate)
                    ->first(['id', 'code', 'name', 'program_id', 'campus_id']);

                if ($existingByCode === null || $this->sameBatch($existingByCode, $row)) {
                    $usedCodes[] = $candidate;

                    return $candidate;
                }
            }

            $candidates = [$baseCode . '-' . $row['id'] . '-' . $suffix];
            $suffix++;
        }
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

    private function normalizeCode(string $code, int $id): string
    {
        $normalized = trim($code);
        $normalized = preg_replace('/\s+/', '-', $normalized) ?? $normalized;

        return $normalized !== '' ? $normalized : 'BATCH-' . $id;
    }

    private function nullableValue(string $value): ?string
    {
        $value = trim($value);

        return strtoupper($value) === 'NULL' ? null : $value;
    }

    /**
     * @param  array<string, int>  $skippedIds
     */
    private function formatSkipSummary(array $skippedIds): string
    {
        ksort($skippedIds);

        return collect($skippedIds)
            ->map(fn (int $count, int $id) => sprintf('%d (%d)', $id, $count))
            ->implode(', ');
    }

    private function resolveInstructor(int $id): string
    {
        return self::INSTRUCTORS[($id - 1) % count(self::INSTRUCTORS)];
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

    /**
     * @param  array{
     *     program_id: int,
     *     campus_id: int,
     *     name: string
     * }  $row
     */
    private function sameBatch(stdClass $batch, array $row): bool
    {
        return (int) $batch->program_id === $row['program_id']
            && (int) $batch->campus_id === $row['campus_id']
            && trim((string) $batch->name) === $row['name'];
    }
}
