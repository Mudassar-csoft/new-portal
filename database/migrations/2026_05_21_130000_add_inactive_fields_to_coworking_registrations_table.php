<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coworking_registrations')) {
            return;
        }

        $columnsToAdd = [
            'leave_date' => fn (Blueprint $table) => $table->date('leave_date')->nullable()->after('status'),
            'used_days' => fn (Blueprint $table) => $table->unsignedInteger('used_days')->nullable()->after('leave_date'),
            'daily_deduction_amount' => fn (Blueprint $table) => $table->decimal('daily_deduction_amount', 12, 2)->default(0)->after('used_days'),
            'usage_deduction_amount' => fn (Blueprint $table) => $table->decimal('usage_deduction_amount', 12, 2)->default(0)->after('daily_deduction_amount'),
            'damage_deduction_amount' => fn (Blueprint $table) => $table->decimal('damage_deduction_amount', 12, 2)->default(0)->after('usage_deduction_amount'),
            'refund_amount' => fn (Blueprint $table) => $table->decimal('refund_amount', 12, 2)->default(0)->after('damage_deduction_amount'),
            'damage_notes' => fn (Blueprint $table) => $table->text('damage_notes')->nullable()->after('refund_amount'),
            'inactive_reason' => fn (Blueprint $table) => $table->string('inactive_reason')->nullable()->after('damage_notes'),
            'inactive_remarks' => fn (Blueprint $table) => $table->text('inactive_remarks')->nullable()->after('inactive_reason'),
            'refund_processed_at' => fn (Blueprint $table) => $table->timestamp('refund_processed_at')->nullable()->after('inactive_remarks'),
        ];

        foreach ($columnsToAdd as $column => $definition) {
            if (Schema::hasColumn('coworking_registrations', $column)) {
                continue;
            }

            Schema::table('coworking_registrations', function (Blueprint $table) use ($definition) {
                $definition($table);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('coworking_registrations')) {
            return;
        }

        $columns = array_values(array_filter([
            'leave_date',
            'used_days',
            'daily_deduction_amount',
            'usage_deduction_amount',
            'damage_deduction_amount',
            'refund_amount',
            'damage_notes',
            'inactive_reason',
            'inactive_remarks',
            'refund_processed_at',
        ], fn (string $column) => Schema::hasColumn('coworking_registrations', $column)));

        if ($columns === []) {
            return;
        }

        Schema::table('coworking_registrations', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
