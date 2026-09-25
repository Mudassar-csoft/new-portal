<?php

namespace App\Http\Controllers;

use App\Models\LeadFollowup;
use App\Support\PendingLeadFollowups;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PendingLeadFollowupController extends Controller
{
    public function __invoke(Request $request, PendingLeadFollowups $pendingFollowups): View
    {
        $followups = $pendingFollowups->forUser($request->user());
        $followups->loadMissing(['lead.program', 'lead.campus']);
        $search = trim((string) $request->query('q', ''));
        $perPage = $request->integer('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        if ($search !== '') {
            $followups = $followups->filter(function (LeadFollowup $followup) use ($search): bool {
                $lead = $followup->lead;

                return Str::contains(Str::lower(implode(' ', [
                    $lead->name,
                    $lead->phone,
                    $lead->program?->title,
                    $lead->program?->name,
                    $lead->campus?->code,
                    $lead->campus?->name,
                ])), Str::lower($search));
            })->values();
        }

        $page = max(1, $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            $followups->forPage($page, $perPage)->values(),
            $followups->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('lead.pending_followups', [
            'followups' => $paginator,
            'search' => $search,
            'perPage' => $perPage,
        ]);
    }
}
