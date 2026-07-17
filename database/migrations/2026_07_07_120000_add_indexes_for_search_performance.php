<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, array{table:string, columns:array<int, string>}>
     */
    private array $indexes = [
        'adm_campus_status_date_idx' => ['table' => 'admissions', 'columns' => ['campus_id', 'student_status', 'admission_date']],
        'adm_student_name_idx' => ['table' => 'admissions', 'columns' => ['student_name']],
        'adm_phone_idx' => ['table' => 'admissions', 'columns' => ['phone']],
        'adm_registration_number_idx' => ['table' => 'admissions', 'columns' => ['registration_number']],

        'reg_campus_registered_idx' => ['table' => 'registrations', 'columns' => ['campus_id', 'registered_at']],
        'reg_student_name_idx' => ['table' => 'registrations', 'columns' => ['student_name']],
        'reg_phone_idx' => ['table' => 'registrations', 'columns' => ['phone']],
        'reg_status_idx' => ['table' => 'registrations', 'columns' => ['status']],

        'leads_name_idx' => ['table' => 'leads', 'columns' => ['name']],
        'leads_phone_idx' => ['table' => 'leads', 'columns' => ['phone']],
        'leads_email_idx' => ['table' => 'leads', 'columns' => ['email']],
        'leads_status_created_idx' => ['table' => 'leads', 'columns' => ['status', 'created_at']],
        'leads_program_created_idx' => ['table' => 'leads', 'columns' => ['program_id', 'created_at']],

        'lead_transfers_status_created_idx' => ['table' => 'lead_transfers', 'columns' => ['status', 'created_at']],
        'lead_transfers_lead_status_idx' => ['table' => 'lead_transfers', 'columns' => ['lead_id', 'status']],

        'web_leads_status_source_submitted_idx' => ['table' => 'web_leads', 'columns' => ['status', 'source_type', 'submitted_at']],
        'web_leads_full_name_idx' => ['table' => 'web_leads', 'columns' => ['full_name']],
        'web_leads_phone_idx' => ['table' => 'web_leads', 'columns' => ['phone']],

        'users_name_idx' => ['table' => 'users', 'columns' => ['name']],
        'users_campus_deleted_idx' => ['table' => 'users', 'columns' => ['campus_id', 'at_deleted']],

        'roles_name_idx' => ['table' => 'roles', 'columns' => ['name']],
        'roles_deleted_idx' => ['table' => 'roles', 'columns' => ['at_deleted']],
        'permissions_resource_action_idx' => ['table' => 'permissions', 'columns' => ['resource', 'action']],
        'permissions_deleted_idx' => ['table' => 'permissions', 'columns' => ['at_deleted']],

        'programs_status_type_idx' => ['table' => 'programs', 'columns' => ['status', 'program_type']],
        'programs_title_idx' => ['table' => 'programs', 'columns' => ['title']],
        'programs_name_idx' => ['table' => 'programs', 'columns' => ['name']],

        'batches_campus_program_status_start_idx' => ['table' => 'batches', 'columns' => ['campus_id', 'program_id', 'status', 'start_date']],
        'batches_name_idx' => ['table' => 'batches', 'columns' => ['name']],
        'batches_instructor_idx' => ['table' => 'batches', 'columns' => ['instructor']],

        'campuses_type_status_city_idx' => ['table' => 'campuses', 'columns' => ['campus_type', 'status', 'city']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $indexName => $definition) {
            $this->addIndexIfPossible($definition['table'], $definition['columns'], $indexName);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->indexes) as $indexName => $definition) {
            $this->dropIndexIfExists($definition['table'], $indexName);
        }
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function addIndexIfPossible(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || $this->hasIndex($tableName, $indexName)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! $this->hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
            [$database, $table, $indexName]
        );

        return $rows !== [];
    }
};
