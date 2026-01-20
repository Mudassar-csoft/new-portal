<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (!Schema::hasColumn('programs', 'program_type')) {
                $table->string('program_type')->nullable()->after('id'); // certificate, diploma, bootcamp, workshop
            }
            if (!Schema::hasColumn('programs', 'title')) {
                $table->string('title')->nullable()->after('program_type');
            }
            if (!Schema::hasColumn('programs', 'fee')) {
                $table->decimal('fee', 12, 2)->nullable()->after('code');
            }
            if (!Schema::hasColumn('programs', 'duration_weeks')) {
                $table->unsignedInteger('duration_weeks')->nullable()->after('fee');
            }
            if (!Schema::hasColumn('programs', 'discount_limit')) {
                $table->decimal('discount_limit', 5, 2)->nullable()->after('duration_weeks');
            }
            if (!Schema::hasColumn('programs', 'installments')) {
                $table->unsignedInteger('installments')->default(1)->after('discount_limit');
            }
            if (!Schema::hasColumn('programs', 'outline_path')) {
                $table->string('outline_path')->nullable()->after('installments');
            }
            if (!Schema::hasColumn('programs', 'prerequisite')) {
                $table->text('prerequisite')->nullable()->after('outline_path');
            }
            if (!Schema::hasColumn('programs', 'remarks')) {
                $table->text('remarks')->nullable()->after('prerequisite');
            }
            if (!Schema::hasColumn('programs', 'status')) {
                $table->string('status')->default('active')->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $dropColumns = [];
            foreach (['program_type','title','fee','duration_weeks','discount_limit','installments','outline_path','prerequisite','remarks','status'] as $col) {
                if (Schema::hasColumn('programs', $col)) {
                    $dropColumns[] = $col;
                }
            }
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
