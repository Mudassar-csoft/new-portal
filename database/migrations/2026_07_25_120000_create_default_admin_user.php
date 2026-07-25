<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        $roleId = DB::table('roles')->where('slug', 'admin')->value('id');

        if (! $roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'Admin',
                'slug' => 'admin',
                'is_system' => true,
                'description' => 'Admin',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        $userId = DB::table('users')->where('email', 'admin@example.com')->value('id');

        if (! $userId) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }

        DB::table('role_user')->updateOrInsert(
            ['user_id' => $userId, 'role_id' => $roleId],
            ['assigned_by' => null, 'created_at' => $timestamp, 'updated_at' => $timestamp]
        );
    }

    public function down(): void
    {
        $userId = DB::table('users')->where('email', 'admin@example.com')->value('id');

        if ($userId) {
            DB::table('role_user')->where('user_id', $userId)->delete();
            DB::table('users')->where('id', $userId)->delete();
        }
    }
};
