<?php

namespace App\Console\Commands;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ImportOldAdmissions extends Command
{
    protected $signature = 'legacy:import-old-admissions {--file= : Full path to old_admissions.sql}';

    protected $description = 'Import legacy old_admissions.sql into the old_admissions table.';

    public function handle(): int
    {
        if (! Schema::hasTable('old_admissions')) {
            $this->error('The old_admissions table does not exist. Run php artisan migrate first.');

            return self::FAILURE;
        }

        $path = $this->resolveSourcePath();
        $processed = 0;
        $buffer = [];

        $this->line('Importing legacy old admissions from: '.$path);

        DB::transaction(function () use ($path, &$processed, &$buffer): void {
            foreach ($this->iterateInsertRows($path) as $row) {
                $buffer[] = $this->normalizeLegacyRow($row);
                $processed++;

                if (count($buffer) >= 500) {
                    $this->flushRows($buffer);
                    $buffer = [];
                }
            }

            if ($buffer !== []) {
                $this->flushRows($buffer);
            }
        });

        $this->info(sprintf(
            'Imported %d legacy old admission rows. Current table count: %d',
            $processed,
            (int) DB::table('old_admissions')->count()
        ));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeLegacyRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => $this->cleanText($row['name']) ?? 'Legacy Student',
            'roll_number' => trim((string) ($row['roll_number'] ?? '')),
            'course' => $this->cleanText($row['course']),
            'cnic' => $this->cleanText($row['cnic']),
            'email' => $this->cleanText($row['email']),
            'primary_contact' => $this->cleanText($row['primary_contact']),
            'batch' => $this->cleanText($row['batch']),
            'campus' => $this->cleanText($row['campus']),
            'status' => $this->cleanText($row['status']) ?? 'Unknown',
            'created_at' => $this->normalizeDateTime($row['created_at'] ?? null),
            'updated_at' => $this->normalizeDateTime($row['updated_at'] ?? null),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function flushRows(array $rows): void
    {
        DB::table('old_admissions')->upsert(
            $rows,
            ['id'],
            [
                'name',
                'roll_number',
                'course',
                'cnic',
                'email',
                'primary_contact',
                'batch',
                'campus',
                'status',
                'created_at',
                'updated_at',
            ]
        );
    }

    private function resolveSourcePath(): string
    {
        $provided = trim((string) $this->option('file'));

        if ($provided !== '') {
            if (is_file($provided)) {
                return $provided;
            }

            throw new RuntimeException('Legacy old admissions file not found: '.$provided);
        }

        $filename = 'old_admissions.sql';
        $candidates = [
            storage_path('app/legacy-import/'.$filename),
            storage_path('app/private/legacy-import/'.$filename),
            storage_path('app/'.$filename),
            base_path('legacy-import/'.$filename),
            database_path('seeders/data/'.$filename),
            'C:/Users/caree/Downloads/'.$filename,
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException(
            'Missing legacy old_admissions.sql. Put it in storage/app/legacy-import, storage/app/private/legacy-import, legacy-import/, database/seeders/data/, or pass --file=/full/path/old_admissions.sql.'
        );
    }

    /**
     * @return Generator<array<string, mixed>>
     */
    private function iterateInsertRows(string $path): Generator
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open legacy SQL dump: '.$path);
        }

        try {
            $insideInsert = false;
            $columns = [];
            $tupleBuffer = '';
            $depth = 0;

            while (($line = fgets($handle)) !== false) {
                if (! $insideInsert) {
                    if (
                        stripos($line, 'INSERT INTO') === false
                        || stripos($line, '`old_admissions`') === false
                        || stripos($line, 'VALUES') === false
                    ) {
                        continue;
                    }

                    $columns = $this->extractInsertColumns($line, $path);
                    $insideInsert = true;
                    $line = substr($line, stripos($line, 'VALUES') + 6);
                }

                $length = strlen($line);

                for ($index = 0; $index < $length; $index++) {
                    $character = $line[$index];

                    if ($depth === 0) {
                        if ($character === '(') {
                            $depth = 1;
                            $tupleBuffer = '';
                        }

                        continue;
                    }

                    if ($character === "'") {
                        $tupleBuffer .= $character;
                        $index++;

                        while (true) {
                            if ($index >= $length) {
                                $line = fgets($handle);

                                if ($line === false) {
                                    throw new RuntimeException('Unterminated quoted string while parsing '.$path.'.');
                                }

                                $length = strlen($line);
                                $index = 0;
                            }

                            $current = $line[$index];
                            $tupleBuffer .= $current;

                            if ($current === '\\') {
                                $index++;

                                if ($index >= $length) {
                                    continue;
                                }

                                $tupleBuffer .= $line[$index];
                                $index++;
                                continue;
                            }

                            if ($current === "'") {
                                if ($index + 1 < $length && $line[$index + 1] === "'") {
                                    $tupleBuffer .= "'";
                                    $index++;

                                    continue;
                                }

                                break;
                            }

                            $index++;
                        }

                        continue;
                    }

                    if ($character === '(') {
                        $depth++;
                        $tupleBuffer .= $character;

                        continue;
                    }

                    if ($character === ')') {
                        $depth--;

                        if ($depth === 0) {
                            $values = $this->parseTupleValues($tupleBuffer);

                            if (count($values) !== count($columns)) {
                                throw new RuntimeException(sprintf(
                                    'Legacy SQL parse error in %s. Expected %d values, got %d.',
                                    $path,
                                    count($columns),
                                    count($values)
                                ));
                            }

                            $combined = array_combine($columns, $values);

                            if ($combined === false) {
                                throw new RuntimeException('Unable to combine parsed values for '.$path.'.');
                            }

                            yield $combined;
                            $tupleBuffer = '';

                            continue;
                        }
                    }

                    $tupleBuffer .= $character;
                }

                if ($insideInsert && $depth === 0 && str_contains($line, ';')) {
                    $insideInsert = false;
                    $columns = [];
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * @return list<string>
     */
    private function extractInsertColumns(string $line, string $path): array
    {
        if (! preg_match('/INSERT INTO\s+`[^`]+`\s*\((.+)\)\s+VALUES/i', $line, $matches)) {
            throw new RuntimeException('Unable to parse INSERT columns from '.$path.'.');
        }

        preg_match_all('/`([^`]+)`/', $matches[1], $columnMatches);

        if (($columnMatches[1] ?? []) === []) {
            throw new RuntimeException('No INSERT columns detected in '.$path.'.');
        }

        return array_values($columnMatches[1]);
    }

    /**
     * @return list<mixed>
     */
    private function parseTupleValues(string $tuple): array
    {
        $values = [];
        $current = '';
        $insideString = false;
        $length = strlen($tuple);

        for ($index = 0; $index < $length; $index++) {
            $character = $tuple[$index];

            if ($insideString) {
                if ($character === '\\') {
                    if ($index + 1 < $length) {
                        $next = $tuple[++$index];
                        $current .= $next === 'n'
                            ? "\n"
                            : ($next === 'r' ? "\r" : $next);
                    }

                    continue;
                }

                if ($character === "'") {
                    if ($index + 1 < $length && $tuple[$index + 1] === "'") {
                        $current .= "'";
                        $index++;

                        continue;
                    }

                    $insideString = false;

                    continue;
                }

                $current .= $character;

                continue;
            }

            if ($character === "'") {
                $insideString = true;

                continue;
            }

            if ($character === ',') {
                $values[] = $this->normalizeParsedValue($current);
                $current = '';

                continue;
            }

            $current .= $character;
        }

        $values[] = $this->normalizeParsedValue($current);

        return $values;
    }

    private function normalizeParsedValue(string $value): mixed
    {
        $trimmed = trim($value);

        if ($trimmed === '' || strcasecmp($trimmed, 'NULL') === 0) {
            return null;
        }

        if (is_numeric($trimmed) && ! str_contains($trimmed, '.')) {
            return (int) $trimmed;
        }

        if (is_numeric($trimmed)) {
            return (float) $trimmed;
        }

        return $trimmed;
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        $string = $this->cleanText($value);

        if ($string === null || preg_match('/^0{4}-0{2}-0{2}/', $string) === 1) {
            return null;
        }

        if (preg_match('/^(\d{4})-\d{2}-\d{2}$/', $string, $matches) === 1) {
            return (int) $matches[1] < 1000 ? null : $string.' 00:00:00';
        }

        if (preg_match('/^(\d{4})-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$/', $string, $matches) === 1) {
            return (int) $matches[1] < 1000 ? null : $string;
        }

        return null;
    }

    private function cleanText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $decoded = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $trimmed = trim($decoded);

        return $trimmed === '' ? null : $trimmed;
    }
}
