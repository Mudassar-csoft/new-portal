<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('coworking_registration_receipts')) {
            return;
        }

        Schema::create('coworking_registration_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coworking_registration_id');
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->string('receipt_type', 40);
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('coworking_registration_id', 'cw_reg_receipts_reg_fk')
                ->references('id')
                ->on('coworking_registrations')
                ->cascadeOnDelete();
            $table->foreign('lead_id', 'cw_reg_receipts_lead_fk')
                ->references('id')
                ->on('leads')
                ->nullOnDelete();
            $table->foreign('campus_id', 'cw_reg_receipts_campus_fk')
                ->references('id')
                ->on('campuses')
                ->nullOnDelete();
            $table->foreign('created_by', 'cw_reg_receipts_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['coworking_registration_id', 'receipt_type'], 'cw_reg_receipts_regid_receiptype_idx');
            $table->index(['campus_id', 'paid_at'], 'cw_reg_receipts_campus_paid_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coworking_registration_receipts');
    }
};
