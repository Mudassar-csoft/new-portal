<?php

namespace App\Console\Commands;

use App\Models\Admission;
use App\Models\FeeCollection;
use App\Models\FinanceOtherCharge;
use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicateStudentRegistrations extends Command
{
    protected $signature = 'students:merge-duplicate-registrations
                            {--write : Apply the merge. Without this option the command only previews changes.}
                            {--phone=* : Limit the merge to one or more specific phone numbers.}';

    protected $description = 'Merge registrations that were split for the same student (matched by phone + CNIC) so all their course admissions live under one registration.';

    public function handle(): int
    {
        $write = (bool) $this->option('write');
        $phoneFilter = collect((array) $this->option('phone'))
            ->map(fn ($phone) => trim((string) $phone))
            ->filter()
            ->values();

        if ($write) {
            $this->warn('Write mode enabled. Duplicate registrations will be relinked and merged.');
        } else {
            $this->info('Preview mode. No data will be changed. Re-run with --write to apply.');
        }

        $groupsQuery = DB::table('registrations')
            ->select('phone', 'cnic', DB::raw('count(*) as c'))
            ->whereNotNull('phone')->where('phone', '!=', '')
            ->whereNotNull('cnic')->where('cnic', '!=', '')
            ->groupBy('phone', 'cnic')
            ->having('c', '>', 1);

        if ($phoneFilter->isNotEmpty()) {
            $groupsQuery->whereIn('phone', $phoneFilter->all());
        }

        $groups = $groupsQuery->orderBy('phone')->get();

        if ($groups->isEmpty()) {
            $this->info('No duplicate (phone + CNIC) registration groups found.');

            return self::SUCCESS;
        }

        $summaryRows = [];
        $flaggedFeeRows = [];
        $mergedGroups = 0;

        foreach ($groups as $group) {
            $registrations = Registration::query()
                ->where('phone', $group->phone)
                ->where('cnic', $group->cnic)
                ->orderByRaw('COALESCE(registered_at, created_at) asc')
                ->orderBy('id')
                ->get();

            if ($registrations->count() < 2) {
                continue;
            }

            $canonical = $registrations->first();
            $duplicates = $registrations->slice(1);

            $admissionCounts = Admission::query()
                ->whereIn('registration_id', $registrations->pluck('id'))
                ->selectRaw('registration_id, count(*) as c')
                ->groupBy('registration_id')
                ->pluck('c', 'registration_id');

            foreach ($duplicates as $duplicate) {
                $movingAdmissions = (int) ($admissionCounts[$duplicate->id] ?? 0);

                $duplicateRegFees = FeeCollection::query()
                    ->where('registration_id', $duplicate->id)
                    ->where('fee_type', 'registration')
                    ->get(['id', 'net_amount', 'status']);

                foreach ($duplicateRegFees as $fee) {
                    $flaggedFeeRows[] = [
                        $duplicate->id,
                        $fee->id,
                        number_format((float) $fee->net_amount, 0),
                        $fee->status,
                    ];
                }

                $summaryRows[] = [
                    $group->phone,
                    $group->cnic,
                    $canonical->id,
                    $duplicate->id,
                    $movingAdmissions,
                    $duplicateRegFees->count(),
                ];

                if (! $write) {
                    continue;
                }

                DB::transaction(function () use ($canonical, $duplicate): void {
                    Admission::query()
                        ->where('registration_id', $duplicate->id)
                        ->update(['registration_id' => $canonical->id]);

                    FeeCollection::query()
                        ->where('registration_id', $duplicate->id)
                        ->where('fee_type', '!=', 'registration')
                        ->update(['registration_id' => $canonical->id]);

                    FinanceOtherCharge::query()
                        ->where('registration_id', $duplicate->id)
                        ->update(['registration_id' => $canonical->id]);

                    $note = sprintf(
                        '[%s] Merged into registration #%d (duplicate registration created for the same student). Course admissions moved; this registration record is kept for audit only.',
                        now()->format('d-M-Y h:i A'),
                        $canonical->id
                    );
                    $existingRemarks = trim((string) $duplicate->remarks);
                    $duplicate->update([
                        'remarks' => $existingRemarks !== '' ? $existingRemarks . PHP_EOL . $note : $note,
                    ]);
                });
            }

            $mergedGroups++;
        }

        $this->newLine();
        $this->line('Duplicate (phone + CNIC) registration groups: ' . $mergedGroups);

        if ($summaryRows !== []) {
            $this->table(
                ['Phone', 'CNIC', 'Canonical Reg #', 'Duplicate Reg #', 'Admissions ' . ($write ? 'Moved' : 'To Move'), 'Reg-Fee Rows Left Behind'],
                $summaryRows
            );
        }

        if ($flaggedFeeRows !== []) {
            $this->newLine();
            $this->warn('The duplicate registrations above each carry their own "Registration Fee" charge (PKR 2000, auto-created by the bug). These are NOT moved or deleted automatically — review with finance before deciding whether to refund/write off.');
            $this->table(['Duplicate Reg #', 'Fee Collection ID', 'Net Amount', 'Status'], $flaggedFeeRows);
        }

        if (! $write) {
            $this->newLine();
            $this->comment('Run with --write after checking the preview above.');
        }

        return self::SUCCESS;
    }
}
