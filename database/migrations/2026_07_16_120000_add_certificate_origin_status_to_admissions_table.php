<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addCertificateStatus = ! Schema::hasColumn('admissions', 'certificate_status');
        $addOriginStatus = ! Schema::hasColumn('admissions', 'certificate_origin_status');

        if ($addCertificateStatus || $addOriginStatus) {
            Schema::table('admissions', function (Blueprint $table) use ($addCertificateStatus, $addOriginStatus): void {
                if ($addCertificateStatus) {
                    $table->string('certificate_status')->nullable()->after('student_status');
                }

                if ($addOriginStatus) {
                    $table->string('certificate_origin_status')->nullable()->after(
                        $addCertificateStatus ? 'certificate_status' : 'student_status'
                    );
                }
            });
        }

        DB::table('admissions')
            ->select('id', 'student_status', 'certificate_origin_status')
            ->whereIn('student_status', [
                'requested',
                'approved',
                'printing',
                'ready',
                'delivered',
            ])
            ->where(function ($query): void {
                $query
                    ->whereNull('certificate_status')
                    ->orWhere('certificate_status', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    $originStatus = trim((string) ($row->certificate_origin_status ?? ''));

                    if ($originStatus === '') {
                        $originStatus = 'enrolled';
                    }

                    DB::table('admissions')
                        ->where('id', $row->id)
                        ->update([
                            'student_status' => $originStatus,
                            'certificate_status' => $row->student_status,
                            'certificate_origin_status' => $originStatus,
                        ]);
                }
            });
    }

    public function down(): void
    {
        $dropCertificateStatus = Schema::hasColumn('admissions', 'certificate_status');
        $dropOriginStatus = Schema::hasColumn('admissions', 'certificate_origin_status');

        if (! $dropCertificateStatus && ! $dropOriginStatus) {
            return;
        }

        Schema::table('admissions', function (Blueprint $table) use ($dropCertificateStatus, $dropOriginStatus): void {
            if ($dropOriginStatus) {
                $table->dropColumn('certificate_origin_status');
            }

            if ($dropCertificateStatus) {
                $table->dropColumn('certificate_status');
            }
        });
    }
};
