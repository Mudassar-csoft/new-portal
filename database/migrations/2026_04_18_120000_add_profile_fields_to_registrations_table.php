<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'guardian_name')) {
                $table->string('guardian_name')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('registrations', 'guardian_phone')) {
                $table->string('guardian_phone')->nullable()->after('guardian_name');
            }

            if (! Schema::hasColumn('registrations', 'cnic')) {
                $table->string('cnic', 13)->nullable()->after('guardian_phone');
            }

            if (! Schema::hasColumn('registrations', 'passport_number')) {
                $table->string('passport_number')->nullable()->after('cnic');
            }

            if (! Schema::hasColumn('registrations', 'education')) {
                $table->string('education')->nullable()->after('email');
            }

            if (! Schema::hasColumn('registrations', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('education');
            }

            if (! Schema::hasColumn('registrations', 'gender')) {
                $table->string('gender', 20)->nullable()->after('date_of_birth');
            }

            if (! Schema::hasColumn('registrations', 'address')) {
                $table->text('address')->nullable()->after('gender');
            }

            if (! Schema::hasColumn('registrations', 'remarks')) {
                $table->text('remarks')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $columns = [
                'guardian_name',
                'guardian_phone',
                'cnic',
                'passport_number',
                'education',
                'date_of_birth',
                'gender',
                'address',
                'remarks',
            ];

            $existingColumns = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('registrations', $column)));

            if ($existingColumns !== []) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};
