<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (! Schema::hasColumn('admissions', 'approval_status')) {
                $table->string('approval_status', 40)->default('approved')->after('student_status');
                $table->index('approval_status');
            }

            if (! Schema::hasColumn('admissions', 'document_cnic_front_path')) {
                $table->string('document_cnic_front_path')->nullable()->after('receipt_number');
            }

            if (! Schema::hasColumn('admissions', 'document_admission_form_path')) {
                $table->string('document_admission_form_path')->nullable()->after('document_cnic_front_path');
            }

            if (! Schema::hasColumn('admissions', 'document_paid_slip_path')) {
                $table->string('document_paid_slip_path')->nullable()->after('document_admission_form_path');
            }

            if (! Schema::hasColumn('admissions', 'documents_uploaded_at')) {
                $table->timestamp('documents_uploaded_at')->nullable()->after('document_paid_slip_path');
            }

            if (! Schema::hasColumn('admissions', 'documents_uploaded_by')) {
                $table->foreignId('documents_uploaded_by')->nullable()->after('documents_uploaded_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('admissions', 'approval_reviewed_at')) {
                $table->timestamp('approval_reviewed_at')->nullable()->after('documents_uploaded_by');
            }

            if (! Schema::hasColumn('admissions', 'approval_reviewed_by')) {
                $table->foreignId('approval_reviewed_by')->nullable()->after('approval_reviewed_at')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('admissions', 'approval_remarks')) {
                $table->text('approval_remarks')->nullable()->after('approval_reviewed_by');
            }
        });

        if (Schema::hasColumn('admissions', 'approval_status')) {
            DB::table('admissions')
                ->whereNull('approval_status')
                ->update(['approval_status' => 'approved']);
        }
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'approval_remarks')) {
                $table->dropColumn('approval_remarks');
            }

            if (Schema::hasColumn('admissions', 'approval_reviewed_by')) {
                $table->dropConstrainedForeignId('approval_reviewed_by');
            }

            if (Schema::hasColumn('admissions', 'approval_reviewed_at')) {
                $table->dropColumn('approval_reviewed_at');
            }

            if (Schema::hasColumn('admissions', 'documents_uploaded_by')) {
                $table->dropConstrainedForeignId('documents_uploaded_by');
            }

            if (Schema::hasColumn('admissions', 'documents_uploaded_at')) {
                $table->dropColumn('documents_uploaded_at');
            }

            if (Schema::hasColumn('admissions', 'document_paid_slip_path')) {
                $table->dropColumn('document_paid_slip_path');
            }

            if (Schema::hasColumn('admissions', 'document_admission_form_path')) {
                $table->dropColumn('document_admission_form_path');
            }

            if (Schema::hasColumn('admissions', 'document_cnic_front_path')) {
                $table->dropColumn('document_cnic_front_path');
            }

            if (Schema::hasColumn('admissions', 'approval_status')) {
                $table->dropIndex(['approval_status']);
                $table->dropColumn('approval_status');
            }
        });
    }
};
