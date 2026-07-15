<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'student.drop'],
            [
                'resource' => 'student',
                'action' => 'drop',
                'description' => 'Drop Student',
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ]
        );

        $permissionId = DB::table('permissions')
            ->where('slug', 'student.drop')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('slug', ['owner', 'admin'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ],
                [
                    'updated_at' => $timestamp,
                    'created_at' => $timestamp,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('permissions')
            ->where('slug', 'student.drop')
            ->delete();
    }
};
