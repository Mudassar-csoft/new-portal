<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lead_followups') || ! Schema::hasColumn('lead_followups', 'next_action_date')) {
            return;
        }

        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dateTime('next_action_date')->nullable()->change();
        });

        DB::table('lead_followups')
            ->join('leads', 'leads.id', '=', 'lead_followups.lead_id')
            ->whereNotNull('lead_followups.next_action_date')
            ->select([
                'lead_followups.id',
                'lead_followups.next_action_date',
                'leads.details',
            ])
            ->orderBy('lead_followups.id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $currentNextActionAt = $this->parseDateTime($row->next_action_date);

                    if (! $currentNextActionAt) {
                        continue;
                    }

                    $details = json_decode((string) $row->details, true);
                    $leadNextFollowupAt = $this->parseDateTime(data_get($details, 'next_followup_at'));

                    if (
                        $leadNextFollowupAt
                        && $currentNextActionAt->format('H:i:s') === '00:00:00'
                        && $leadNextFollowupAt->isSameDay($currentNextActionAt)
                    ) {
                        DB::table('lead_followups')
                            ->where('id', $row->id)
                            ->update([
                                'next_action_date' => $leadNextFollowupAt->format('Y-m-d H:i:s'),
                            ]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lead_followups') || ! Schema::hasColumn('lead_followups', 'next_action_date')) {
            return;
        }

        Schema::table('lead_followups', function (Blueprint $table) {
            $table->date('next_action_date')->nullable()->change();
        });
    }

    private function parseDateTime(mixed $value): ?Carbon
    {
        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            return null;
        }

        foreach (['Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'] as $format) {
            try {
                $dateTime = Carbon::createFromFormat($format, $stringValue);

                return $format === 'Y-m-d'
                    ? $dateTime->startOfDay()
                    : $dateTime;
            } catch (Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($stringValue);
        } catch (Throwable) {
            return null;
        }
    }
};
