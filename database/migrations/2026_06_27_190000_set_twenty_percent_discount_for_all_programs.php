<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('programs') || !Schema::hasTable('program_campus_discounts')) {
            return;
        }

        $now = now();

        DB::transaction(function () use ($now): void {
            // Normalize any existing campus-specific or global discount rows to 20%.
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
        });
    }

    public function down(): void
    {
        // Irreversible data migration. Existing discount values are intentionally not restored.
    }
};
