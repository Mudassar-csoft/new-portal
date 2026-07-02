<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadFollowup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillLeadInitialFollowupRemarks extends Command
{
    private const AUTO_NOTE = 'Initial follow-up created automatically.';

    protected $signature = 'leads:backfill-initial-followup-remarks
                            {--write : Apply the updates. Without this option the command only previews changes.}
                            {--lead-id=* : Limit the backfill to one or more specific lead ids.}
                            {--chunk=200 : Number of leads to process per chunk.}';

    protected $description = 'Backfill old lead initial follow-up remarks from lead details without affecting later follow-ups.';

    public function handle(): int
    {
        $write = (bool) $this->option('write');
        $leadIds = collect((array) $this->option('lead-id'))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->values();
        $chunkSize = max(1, (int) $this->option('chunk'));

        if ($write) {
            $this->warn('Write mode enabled. Only the first follow-up row with the old automatic note will be updated.');
        } else {
            $this->info('Preview mode. No data will be changed. Re-run with --write to apply updates.');
        }

        $query = Lead::query()
            ->select(['id', 'details'])
            ->whereNotNull('details');

        if ($leadIds->isNotEmpty()) {
            $query->whereIn('id', $leadIds->all());
        }

        $scanned = 0;
        $eligible = 0;
        $updated = 0;
        $skippedNoRemarks = 0;
        $skippedNoFollowup = 0;
        $skippedAlreadyChanged = 0;
        $previewRows = [];

        $query
            ->orderBy('id')
            ->chunkById($chunkSize, function ($leads) use (
                $write,
                &$scanned,
                &$eligible,
                &$updated,
                &$skippedNoRemarks,
                &$skippedNoFollowup,
                &$skippedAlreadyChanged,
                &$previewRows
            ): void {
                foreach ($leads as $lead) {
                    $scanned++;

                    $remarks = trim((string) data_get($lead->details, 'remarks', ''));
                    if ($remarks === '') {
                        $skippedNoRemarks++;
                        continue;
                    }

                    $firstFollowup = LeadFollowup::query()
                        ->where('lead_id', $lead->id)
                        ->orderBy('created_at')
                        ->orderBy('id')
                        ->first(['id', 'lead_id', 'note']);

                    if (! $firstFollowup) {
                        $skippedNoFollowup++;
                        continue;
                    }

                    if (trim((string) $firstFollowup->note) !== self::AUTO_NOTE) {
                        $skippedAlreadyChanged++;
                        continue;
                    }

                    $eligible++;

                    if (count($previewRows) < 20) {
                        $previewRows[] = [
                            'lead_id' => $lead->id,
                            'followup_id' => $firstFollowup->id,
                            'old_note' => $firstFollowup->note,
                            'new_note' => $remarks,
                        ];
                    }

                    if (! $write) {
                        continue;
                    }

                    DB::transaction(function () use ($firstFollowup, $remarks): void {
                        LeadFollowup::query()
                            ->whereKey($firstFollowup->id)
                            ->where('note', self::AUTO_NOTE)
                            ->update(['note' => $remarks]);
                    });

                    $updated++;
                }
            });

        $this->newLine();
        $this->line('Summary');
        $this->table(
            ['Scanned', 'Eligible', $write ? 'Updated' : 'Would Update', 'No Remarks', 'No Followup', 'Skipped Changed'],
            [[
                $scanned,
                $eligible,
                $write ? $updated : $eligible,
                $skippedNoRemarks,
                $skippedNoFollowup,
                $skippedAlreadyChanged,
            ]]
        );

        if ($previewRows !== []) {
            $this->newLine();
            $this->line('Preview of first eligible rows');
            $this->table(['Lead ID', 'Followup ID', 'Old Note', 'New Note'], $previewRows);
        } else {
            $this->newLine();
            $this->info('No eligible leads found.');
        }

        if (! $write) {
            $this->newLine();
            $this->comment('Run with --write after checking the preview.');
        }

        return self::SUCCESS;
    }
}
