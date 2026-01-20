<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadTransfer;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function create(): View
    {
        $campuses = Campus::orderBy('name')->get();
        $programs = Program::orderBy('title')->get();
        $origins = ['Walk-In', 'WhatsApp Business', 'Facebook', 'Google Business', 'Website', 'Instagram', 'LinkedIn', 'Referral', 'Other'];
        $marketingSources = ['Alumni', 'Career team', 'Event/ Expo', 'Email', 'Facebook', 'Google', 'Instagram', 'LinkedIn', 'Referral', 'Other'];

        return view('lead.create', compact('campuses', 'programs', 'origins', 'marketingSources'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'marketing_source' => ['nullable', 'string', 'max:255'],
            'details' => ['array'],
        ]);

        $details = $validated['details'] ?? [];
        $initialProbability = $details['probability'] ?? null;
        $initialNext = $details['next_followup_at'] ?? null;

        $lead = Lead::create([
            'campus_id' => $validated['campus_id'] ?? null,
            'program_id' => $validated['program_id'] ?? null,
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'type' => $validated['type'] ?? null,
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'origin' => $validated['origin'] ?? null,
            'marketing_source' => $validated['marketing_source'] ?? null,
            'status' => 'pending',
            'details' => $details,
        ]);

        LeadFollowup::create([
            'lead_id' => $lead->id,
            'campus_id' => $lead->campus_id,
            'user_id' => $request->user()?->id,
            'note' => 'Initial follow-up created automatically.',
            'method' => null,
            'probability' => $initialProbability,
            'next_action_date' => $initialNext,
            'stage' => 'new',
            'lead_status' => 'pending',
        ]);

        return Redirect::route('leads.create')->with('status', 'Lead created with initial follow-up.');
    }

    public function addFollowup(Request $request, Lead $lead): RedirectResponse
    {
        if (in_array($lead->status, ['registered', 'not_interesting'], true)) {
            return Redirect::back()->with('error', 'This lead is already ' . str_replace('_', ' ', $lead->status) . '; no further follow-ups allowed.');
        }

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'method' => ['nullable', 'string', 'max:50'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'note' => ['nullable', 'string'],
            'next_action_date' => ['nullable', 'date'],
            'stage' => ['required', Rule::in(['new', 'contacted', 'need_analysis', 'branch_visited', 'proposal_negotiation', 'not_interesting', 'registered'])],
        ]);

        $followup = LeadFollowup::create([
            'lead_id' => $lead->id,
            'campus_id' => $validated['campus_id'] ?? $lead->campus_id,
            'user_id' => $request->user()?->id,
            'method' => $validated['method'] ?? null,
            'probability' => $validated['probability'] ?? null,
            'note' => $validated['note'] ?? null,
            'next_action_date' => $validated['next_action_date'] ?? null,
            'stage' => $validated['stage'],
            'lead_status' => $lead->status,
        ]);

        if (in_array($validated['stage'], ['registered', 'not_interesting'], true)) {
            $lead->update(['status' => $validated['stage'] === 'registered' ? 'registered' : 'not_interesting']);
        }

        return Redirect::back()->with('status', 'Follow-up added.');
    }

    public function show(Lead $lead): View
    {
        $lead->load([
            'campus',
            'program',
            'followups.campus',
            'followups.user',
            'transfers.fromCampus',
            'transfers.toCampus',
            'transfers.requester',
            'transfers.approver',
        ]);

        $stages = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'need_analysis' => 'Need Analysis',
            'branch_visited' => 'Branch Visited',
            'proposal_negotiation' => 'Proposal / Negotiation',
            'not_interesting' => 'Not Interesting',
            'registered' => 'Registered',
        ];

        $followups = $lead->followups->sortByDesc('created_at')->values();
        $currentStage = $followups->first()->stage ?? 'new';
        $latestFollowup = $followups->first();
        $nextFollowup = $followups->firstWhere('next_action_date', '!=', null);
        $campuses = Campus::orderBy('name')->get();

        // Hide the opposite terminal state to avoid showing both end states together
        if ($lead->status === 'not_interesting') {
            unset($stages['registered']);
        } elseif ($lead->status === 'registered') {
            unset($stages['not_interesting']);
        }

        if (!array_key_exists($currentStage, $stages)) {
            $currentStage = array_key_first($stages);
        }

        return view('lead.show', [
            'lead' => $lead,
            'followups' => $followups,
            'stages' => $stages,
            'currentStage' => $currentStage,
            'latestFollowup' => $latestFollowup,
            'nextFollowup' => $nextFollowup,
            'campuses' => $campuses,
            'transfers' => $lead->transfers()->latest()->get(),
        ]);
    }

    public function transferForm(Lead $lead): View
    {
        if ($lead->status === 'registered') {
            abort(403, 'Registered leads cannot be transferred.');
        }
        $campuses = Campus::orderBy('name')->get();
        return view('lead.transfer', compact('lead', 'campuses'));
    }

    public function transferStore(Request $request, Lead $lead): RedirectResponse
    {
        if ($lead->status === 'registered') {
            return Redirect::back()->withErrors(['transfer' => 'Registered leads cannot be transferred.']);
        }
        $validated = $request->validate([
            'to_campus_id' => ['required', 'exists:campuses,id', 'different:from_campus_id'],
            'reason' => ['nullable', 'string'],
        ]);

        if ($lead->campus_id == $validated['to_campus_id']) {
            return Redirect::back()->withErrors(['to_campus_id' => 'Lead is already in the selected campus.']);
        }

        LeadTransfer::create([
            'lead_id' => $lead->id,
            'from_campus_id' => $lead->campus_id,
            'to_campus_id' => $validated['to_campus_id'],
            'transferred_by' => $request->user()?->id,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return Redirect::route('leads.show', $lead)->with('status', 'Transfer request submitted for approval.');
    }

    public function approveTransfer(Request $request, LeadTransfer $transfer): RedirectResponse
    {
        if ($transfer->status === 'approved') {
            return Redirect::back()->with('status', 'Transfer already approved.');
        }

        $transfer->update([
            'status' => 'approved',
            'approved_by' => $request->user()?->id,
            'approved_at' => now(),
        ]);

        $lead = $transfer->lead;
        $lead->update(['campus_id' => $transfer->to_campus_id]);

        LeadFollowup::create([
            'lead_id' => $lead->id,
            'campus_id' => $transfer->to_campus_id,
            'user_id' => $request->user()?->id,
            'method' => 'transfer',
            'note' => 'Lead transferred from ' . ($transfer->fromCampus->name ?? 'N/A') . ' to ' . ($transfer->toCampus->name ?? 'N/A'),
            'stage' => 'contacted',
            'lead_status' => $lead->status,
        ]);

        return Redirect::back()->with('status', 'Transfer approved and campus updated.');
    }

    public function followups(): View
    {
        $followups = LeadFollowup::with(['lead.program', 'lead.campus'])
            ->latest()
            ->get()
            ->unique('lead_id') // only keep the latest follow-up per lead
            ->values()
            ->map(function ($f) {
                $stageMap = [
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'need_analysis' => 'Need Analysis',
                    'branch_visited' => 'Branch Visited',
                    'proposal_negotiation' => 'Proposal or Negotiation',
                    'not_interesting' => 'Not Interesting',
                    'registered' => 'Registered',
                ];
                $f->stage_label = $stageMap[$f->stage] ?? ucfirst(str_replace('_', ' ', $f->stage));
                return $f;
            });

        $tabs = [
            'all' => 'All',
            'New' => 'New',
            'Contacted' => 'Contacted',
            'Need Analysis' => 'Need Analysis',
            'Branch Visited' => 'Branch Visited',
            'Proposal or Negotiation' => 'Proposal or Negotiation',
            'Not Interesting' => 'Not Interesting',
            'Registered' => 'Registered',
        ];

        $badgeColors = [
            'all' => 'badge-secondary',
            'New' => 'badge-primary',
            'Contacted' => 'badge-success',
            'Need Analysis' => 'badge-warning',
            'Branch Visited' => 'badge-default',
            'Proposal or Negotiation' => 'badge-info',
            'Not Interesting' => 'badge-default',
            'Registered' => 'badge-success',
        ];

        $tabCounts = [];
        foreach ($tabs as $key => $label) {
            $tabCounts[$key] = $key === 'all'
                ? $followups->count()
                : $followups->where('stage_label', $label)->count();
        }

        return view('lead.followups', compact('followups', 'tabs', 'badgeColors', 'tabCounts'));
    }
}
