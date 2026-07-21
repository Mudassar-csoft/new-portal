<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'lead.followup.not-interesting'],
            [
                'resource' => 'lead',
                'action' => 'followup.not-interesting',
                'description' => 'Follow-up on Not Interested Leads',
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('slug', 'lead.followup.not-interesting')
            ->delete();
    }
};
