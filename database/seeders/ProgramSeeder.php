<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use stdClass;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $usedCodes = [];

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
                'discount_limit' => $row['discount_limit'],
                'installments' => 1,
                'outline_path' => $row['outline_path'],
                'prerequisite' => $row['prerequisite'],
                'remarks' => $row['remarks'],
                'status' => 'active',
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

                continue;
            }

            $existingByCode = DB::table('programs')
                ->where('code', $code)
                ->first(['id', 'code', 'title', 'name']);

            if ($existingByCode && $this->sameProgram($existingByCode, $row['title'])) {
                DB::table('programs')
                    ->where('id', $existingByCode->id)
                    ->update($this->withoutId($payload));

                continue;
            }

            if ($existingById) {
                DB::table('programs')->insert($this->withoutId($payload));

                $this->command?->warn(sprintf(
                    'Program %s inserted without fixed id %d because that id is already in use.',
                    $code,
                    $row['id']
                ));

                continue;
            }

            DB::table('programs')->insert($payload);
        }
    }

    /**
     * @return list<array{
     *     id: int,
     *     title: string,
     *     program_type: string,
     *     base_code: string,
     *     fee: float,
     *     duration_weeks: int,
     *     discount_limit: float|null,
     *     outline_path: string|null,
     *     prerequisite: string|null,
     *     remarks: string|null,
     *     created_at: string|null,
     *     updated_at: string|null
     * }>
     */
    private function programRows(): array
    {
        $path = __DIR__ . '/data/programs_ongoing_dump.sql';

        if (!is_file($path)) {
            throw new RuntimeException('Program dump seed data file is missing.');
        }

        $rows = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);

            if ($line === '' || !str_starts_with($line, '(')) {
                continue;
            }

            $columns = str_getcsv(trim($line, " \t\n\r\0\x0B(),;"), ',', "'", '\\');

            if (count($columns) !== 17) {
                throw new RuntimeException('Invalid program dump row: ' . $line);
            }

            $status = $this->nullableValue($columns[13]);

            if (!is_string($status) || Str::lower($status) !== 'on going') {
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
                'discount_limit' => ($discount = $this->nullableValue($columns[9])) !== null ? (float) $discount : null,
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
     *     id: int,
     *     title: string,
     *     base_code: string
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
}
