<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WipeDataKeepAdmins extends Command
{
    protected $signature = 'app:wipe-data-keep-admins
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Delete all application data except active admin and owner users.';

    /**
     * @var list<string>
     */
    private array $preservedTables = [
        'migrations',
        'users',
        'roles',
        'permissions',
        'role_user',
        'permission_role',
        'permission_user',
        'sqlite_sequence',
    ];

    public function handle(): int
    {
        $admins = User::query()
            ->select(['id', 'name', 'email', 'campus_id'])
            ->with('roles:id,slug')
            ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['owner', 'admin']))
            ->get();

        if ($admins->isEmpty()) {
            $this->error('No active admin or owner user was found. Aborting to avoid locking you out.');

            return self::FAILURE;
        }

        $this->warn('Preserving admin users: '.$admins->pluck('email')->implode(', '));

        if (! $this->option('force') && ! $this->confirm('Delete all other application data?')) {
            $this->line('Cancelled.');

            return self::SUCCESS;
        }

        $tablesToWipe = $this->resolveTablesToWipe();

        try {
            $this->disableForeignKeyChecks();

            $this->detachAdminsFromCampuses($admins->pluck('id'));
            $this->wipeTables($tablesToWipe);
            $this->cleanupUserPivotTables($admins->pluck('id'));
            $this->deleteNonAdminUsers($admins->pluck('id'));
        } catch (Throwable $exception) {
            $this->enableForeignKeyChecks();

            throw $exception;
        }

        $this->enableForeignKeyChecks();

        $this->info(sprintf(
            'Wipe complete. Preserved %d admin user(s).',
            $admins->count()
        ));

        return self::SUCCESS;
    }

    private function detachAdminsFromCampuses(Collection $adminIds): void
    {
        User::query()
            ->whereIn('id', $adminIds->all())
            ->update(['campus_id' => null]);
    }

    private function wipeTables(Collection $tablesToWipe): void
    {
        foreach ($tablesToWipe as $table) {
            DB::table($table)->truncate();
        }
    }

    private function cleanupUserPivotTables(Collection $adminIds): void
    {
        DB::table('role_user')
            ->whereNotIn('user_id', $adminIds->all())
            ->delete();

        DB::table('role_user')
            ->whereNotNull('assigned_by')
            ->whereNotIn('assigned_by', $adminIds->all())
            ->update(['assigned_by' => null]);

        DB::table('permission_user')
            ->whereNotIn('user_id', $adminIds->all())
            ->delete();
    }

    private function deleteNonAdminUsers(Collection $adminIds): void
    {
        User::withoutGlobalScope('not_deleted')
            ->whereNotIn('id', $adminIds->all())
            ->delete();
    }

    private function disableForeignKeyChecks(): void
    {
        match (DB::getDriverName()) {
            'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=0'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = OFF'),
            'pgsql' => DB::statement('SET session_replication_role = replica'),
            default => null,
        };
    }

    private function enableForeignKeyChecks(): void
    {
        match (DB::getDriverName()) {
            'mysql' => DB::statement('SET FOREIGN_KEY_CHECKS=1'),
            'sqlite' => DB::statement('PRAGMA foreign_keys = ON'),
            'pgsql' => DB::statement('SET session_replication_role = DEFAULT'),
            default => null,
        };
    }

    private function resolveTablesToWipe(): Collection
    {
        $driver = DB::getDriverName();
        $database = DB::getDatabaseName();

        return collect(Schema::getTables())
            ->filter(function (array $table) use ($database, $driver) {
                if ($driver !== 'mysql') {
                    return true;
                }

                return ($table['schema'] ?? null) === $database;
            })
            ->map(fn (array $table) => $table['name'])
            ->reject(fn (string $table) => in_array($table, $this->preservedTables, true))
            ->values();
    }
}
