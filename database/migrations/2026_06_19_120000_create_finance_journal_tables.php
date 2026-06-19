<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('finance_journal_entries')) {
            Schema::create('finance_journal_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->string('journal_no')->unique();
                $table->date('entry_date')->index();
                $table->string('entry_type', 60)->index();
                $table->string('source_type', 180)->nullable();
                $table->unsignedBigInteger('source_id')->nullable();
                $table->string('event_key', 190)->unique();
                $table->string('reference_number')->nullable()->index();
                $table->text('description')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['campus_id', 'entry_date'], 'finance_journal_entries_campus_date_idx');
                $table->index(['source_type', 'source_id'], 'finance_journal_entries_source_idx');
            });
        }

        if (!Schema::hasTable('finance_journal_lines')) {
            Schema::create('finance_journal_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finance_journal_entry_id')->constrained('finance_journal_entries')->cascadeOnDelete();
                $table->unsignedSmallInteger('line_no')->default(1);
                $table->string('account_code', 30)->index();
                $table->string('account_name', 150);
                $table->decimal('debit_amount', 14, 2)->default(0);
                $table->decimal('credit_amount', 14, 2)->default(0);
                $table->string('memo')->nullable();
                $table->timestamps();

                $table->index(['account_code', 'finance_journal_entry_id'], 'finance_journal_lines_account_entry_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_journal_lines');
        Schema::dropIfExists('finance_journal_entries');
    }
};
