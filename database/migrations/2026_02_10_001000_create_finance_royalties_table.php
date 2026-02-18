<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finance_royalties')) {
            return;
        }

        Schema::create('finance_royalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->decimal('royalty_rate', 5, 2)->default(0);
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['campus_id', 'admission_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_royalties');
    }
};
