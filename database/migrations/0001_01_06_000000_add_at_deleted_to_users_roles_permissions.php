<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('at_deleted')->nullable()->after('remember_token');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->timestamp('at_deleted')->nullable()->after('is_system');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->timestamp('at_deleted')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('at_deleted');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('at_deleted');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('at_deleted');
        });
    }
};
