<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            if (!Schema::hasColumn('campuses', 'code')) {
                $table->string('code')->unique()->after('slug');
            }
            if (!Schema::hasColumn('campuses', 'title')) {
                $table->string('title')->nullable()->after('name');
            }
            if (!Schema::hasColumn('campuses', 'country')) {
                $table->string('country')->nullable()->after('city');
            }
            if (!Schema::hasColumn('campuses', 'city_abbr')) {
                $table->string('city_abbr', 10)->nullable()->after('city');
            }
            if (!Schema::hasColumn('campuses', 'campus_type')) {
                $table->string('campus_type')->default('company')->after('city_abbr'); // company, franchise
            }
            if (!Schema::hasColumn('campuses', 'campus_email')) {
                $table->string('campus_email')->nullable()->after('campus_type');
            }
            if (!Schema::hasColumn('campuses', 'landline')) {
                $table->string('landline')->nullable()->after('campus_email');
            }
            if (!Schema::hasColumn('campuses', 'mobile')) {
                $table->string('mobile')->nullable()->after('landline');
            }
            if (!Schema::hasColumn('campuses', 'address')) {
                $table->text('address')->nullable()->after('mobile');
            }
            if (!Schema::hasColumn('campuses', 'labs_count')) {
                $table->unsignedInteger('labs_count')->default(0)->after('address');
            }
            if (!Schema::hasColumn('campuses', 'royalty_rate')) {
                $table->decimal('royalty_rate', 5, 2)->nullable()->after('labs_count');
            }
            if (!Schema::hasColumn('campuses', 'remarks')) {
                $table->text('remarks')->nullable()->after('royalty_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'title',
                'country',
                'city_abbr',
                'campus_type',
                'campus_email',
                'landline',
                'mobile',
                'address',
                'labs_count',
                'royalty_rate',
                'remarks',
            ]);
        });
    }
};
