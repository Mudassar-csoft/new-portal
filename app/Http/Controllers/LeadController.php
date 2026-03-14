<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadTransfer;
use App\Models\Program;
use App\Models\WebLead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    public function index(): View
    {
        $leads = $this->trainingLeadQuery()
            ->with(['program'])
            ->latest()
            ->get();

        $tabs = [
            'all' => 'All Leads',
            'pending' => 'Pending',
            'registered' => 'Registered',
            'enrolled' => 'Enrolled',
            'not_interesting' => 'Not Interested',
        ];

        $badgeColors = [
            'all' => 'badge-secondary',
            'pending' => 'badge-primary',
            'registered' => 'badge-info',
            'enrolled' => 'badge-warning',
            'not_interesting' => 'badge-danger',
        ];

        $tabCounts = [];
        foreach ($tabs as $key => $label) {
            $tabCounts[$key] = $key === 'all'
                ? $leads->count()
                : $leads->where('status', $key)->count();
        }

        return view('lead.all', compact('leads', 'tabs', 'badgeColors', 'tabCounts'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $webLead = null;
        $leadPrefill = [];

        if ($request->filled('web_lead')) {
            $webLead = WebLead::query()->with('convertedLead')->findOrFail($request->integer('web_lead'));

            if ($webLead->status === WebLead::STATUS_NOT_INTERESTED) {
                return Redirect::route('web-leads.show', $webLead)
                    ->with('error', 'This web lead is already marked as not interested.');
            }

            if ($webLead->status === WebLead::STATUS_LEAD_CREATED && $webLead->converted_to_lead_id) {
                return Redirect::route('leads.show', $webLead->converted_to_lead_id)
                    ->with('status', 'A CRM lead has already been created from this web lead.');
            }

            $leadPrefill = $this->buildWebLeadPrefill($webLead);
        }

        $campuses = Campus::orderBy('name')->get();
        $programs = Program::orderBy('title')->get();
        $origins = ['Walk-In', 'WhatsApp Business', 'Facebook', 'Google Business', 'Website', 'Instagram', 'LinkedIn', 'Referral', 'Other'];
        $marketingSources = ['Alumni', 'Career team', 'Event/ Expo', 'Email', 'Facebook', 'Google', 'Instagram', 'LinkedIn', 'Referral', 'Website', 'Other'];

        return view('lead.create', compact('campuses', 'programs', 'origins', 'marketingSources', 'webLead', 'leadPrefill'));
    }

    public function store(Request $request): RedirectResponse
    {
        $webLead = null;

        if ($request->filled('web_lead_id')) {
            $webLead = WebLead::query()->find($request->integer('web_lead_id'));

            if ($webLead?->status === WebLead::STATUS_NOT_INTERESTED) {
                return Redirect::route('web-leads.show', $webLead)
                    ->with('error', 'This web lead is marked as not interested and cannot be converted.');
            }

            if ($webLead?->status === WebLead::STATUS_LEAD_CREATED && $webLead->converted_to_lead_id) {
                return Redirect::route('leads.show', $webLead->converted_to_lead_id)
                    ->with('status', 'A CRM lead has already been created from this web lead.');
            }
        }

        $isTraining = $request->input('type') === 'training';

        $validated = $request->validate([
            'web_lead_id' => ['nullable', 'exists:web_leads,id'],
            'program_id' => ['nullable', Rule::requiredIf($isTraining), 'exists:programs,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'type' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:50', 'unique:leads,phone'],
            'city' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:255'],
            'origin' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:255'],
            'marketing_source' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:255'],
            'campus_id' => ['nullable', Rule::requiredIf($isTraining), 'exists:campuses,id'],
            'details' => ['array'],
            'details.country' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:255'],
            'details.area' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:255'],
            'details.teaching_method' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:50'],
            'details.gender' => ['nullable', Rule::requiredIf($isTraining), 'string', 'max:50'],
            'details.next_followup_at' => ['nullable', Rule::requiredIf($isTraining), 'date'],
            'details.probability' => ['nullable', Rule::requiredIf($isTraining), 'integer', 'min:0', 'max:100'],
            'details.remarks' => ['nullable', Rule::requiredIf($isTraining), 'string'],
        ]);

        try {
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

            if ($webLead) {
                $webLead->update([
                    'status' => WebLead::STATUS_LEAD_CREATED,
                    'converted_to_lead_id' => $lead->id,
                    'handled_by' => $request->user()?->id,
                    'handled_at' => now(),
                ]);
            }

            return Redirect::route('leads.followups')->with('status', 'Lead created with initial follow-up.');
        } catch (Throwable $e) {
            report($e);
            return Redirect::back()
                ->withInput()
                ->with('error', 'Unable to save the lead right now. Please try again.');
        }
    }

    public function addFollowup(Request $request, Lead $lead): RedirectResponse
    {
        if (in_array($lead->status, ['registered', 'not_interesting', 'enrolled'], true)) {
            return Redirect::back()->with('error', 'This lead is already ' . str_replace('_', ' ', $lead->status) . '; no further follow-ups allowed.');
        }

        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'method' => ['nullable', 'string', 'max:50'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'note' => ['nullable', 'string'],
            'next_action_date' => ['nullable', 'date'],
            'stage' => ['required', Rule::in(['new', 'contacted', 'need_analysis', 'branch_visited', 'proposal_negotiation', 'not_interesting', 'registered', 'enroll'])],
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

        if (in_array($validated['stage'], ['registered', 'not_interesting', 'enroll'], true)) {
            $lead->update([
                'status' => match ($validated['stage']) {
                    'registered' => 'registered',
                    'enroll' => 'enrolled',
                    default => 'not_interesting',
                },
            ]);
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
            'enroll' => 'Enrolled',
        ];

        $followups = $lead->followups->sortByDesc('created_at')->values();
        $currentStage = $followups->first()->stage ?? 'new';
        $latestFollowup = $followups->first();
        $nextFollowup = $followups->firstWhere('next_action_date', '!=', null);
        $campuses = Campus::orderBy('name')->get();

        // Hide the opposite terminal state to avoid showing both end states together
        if ($lead->status === 'not_interesting') {
            unset($stages['registered']);
            unset($stages['enroll']);
        } elseif ($lead->status === 'registered') {
            unset($stages['not_interesting']);
        } elseif ($lead->status === 'enrolled') {
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
        if (in_array($lead->status, ['registered', 'enrolled'], true)) {
            abort(403, 'Registered or enrolled leads cannot be transferred.');
        }
        $campuses = Campus::orderBy('name')->get();
        return view('lead.transfer', compact('lead', 'campuses'));
    }

    public function transferStore(Request $request, Lead $lead): RedirectResponse
    {
        if (in_array($lead->status, ['registered', 'enrolled'], true)) {
            return Redirect::back()->withErrors(['transfer' => 'Registered or enrolled leads cannot be transferred.']);
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

        return Redirect::route('leads.transfer')->with('status', 'Transfer request submitted for approval.');
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

    public function transfers(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            $query = LeadTransfer::query()
                ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->training())
                ->with([
                    'lead:id,name,phone,program_id',
                    'lead.program:id,title,name',
                    'fromCampus:id,name,code',
                    'toCampus:id,name,code',
                    'requester:id,name',
                    'approver:id,name',
                ])
                ->select('lead_transfers.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('lead_name', function (LeadTransfer $transfer) {
                    if (!$transfer->lead) {
                        return 'N/A';
                    }

                    $url = route('leads.show', $transfer->lead->id);
                    $name = e($transfer->lead->name ?? 'N/A');

                    return '<a href="' . e($url) . '" class="lead-link">' . $name . '</a>';
                })
                ->addColumn('lead_phone', fn (LeadTransfer $transfer) => e(optional($transfer->lead)->phone ?? 'N/A'))
                ->addColumn(
                    'program',
                    fn (LeadTransfer $transfer) => e(optional(optional($transfer->lead)->program)->title
                        ?? optional(optional($transfer->lead)->program)->name
                        ?? 'N/A')
                )
                ->addColumn('from_campus', fn (LeadTransfer $transfer) => e(optional($transfer->fromCampus)->code ?? optional($transfer->fromCampus)->name ?? 'N/A'))
                ->addColumn('to_campus', fn (LeadTransfer $transfer) => e(optional($transfer->toCampus)->code ?? optional($transfer->toCampus)->name ?? 'N/A'))
                ->addColumn('status_badge', function (LeadTransfer $transfer) {
                    $status = strtolower((string) ($transfer->status ?? 'pending'));
                    $class = match ($status) {
                        'approved' => 'label-success',
                        'rejected' => 'label-danger',
                        default => 'label-warning',
                    };

                    return '<span class="label ' . $class . '">' . e(ucfirst($status)) . '</span>';
                })
                ->addColumn('requested_by', fn (LeadTransfer $transfer) => e(optional($transfer->requester)->name ?? 'N/A'))
                ->editColumn('created_at', fn (LeadTransfer $transfer) => optional($transfer->created_at)->format('d-M-Y H:i') ?? 'N/A')
                ->addColumn('approved_by', fn (LeadTransfer $transfer) => e(optional($transfer->approver)->name ?? 'N/A'))
                ->addColumn('approved_at', fn (LeadTransfer $transfer) => optional($transfer->approved_at)->format('d-M-Y H:i') ?? 'N/A')
                ->editColumn('reason', fn (LeadTransfer $transfer) => e($transfer->reason ?? 'N/A'))
                ->addColumn('actions', fn (LeadTransfer $transfer) => view('lead.partials.transfer-grid-action', ['transfer' => $transfer])->render())
                ->filterColumn('lead_name', function ($query, $keyword) {
                    $query->whereHas('lead', function ($leadQuery) use ($keyword) {
                        $leadQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('lead_phone', function ($query, $keyword) {
                    $query->whereHas('lead', function ($leadQuery) use ($keyword) {
                        $leadQuery->where('phone', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('from_campus', function ($query, $keyword) {
                    $query->whereHas('fromCampus', function ($campusQuery) use ($keyword) {
                        $campusQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('code', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('to_campus', function ($query, $keyword) {
                    $query->whereHas('toCampus', function ($campusQuery) use ($keyword) {
                        $campusQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('code', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['lead_name', 'status_badge', 'actions'])
                ->make(true);
        }

        return view('lead.transfers');
    }

    public function followups(): View
    {
        $followups = $this->latestTrainingFollowups();

        $tabs = [
            'all' => 'All',
            'New' => 'New',
            'Contacted' => 'Contacted',
            'Need Analysis' => 'Need Analysis',
            'Branch Visited' => 'Branch Visited',
            'Proposal or Negotiation' => 'Proposal or Negotiation',
            'Not Interesting' => 'Not Interesting',
            'Registered' => 'Registered',
            'Enrolled' => 'Enrolled',
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
            'Enrolled' => 'badge-success',
        ];

        $tabCounts = [];
        foreach ($tabs as $key => $label) {
            $tabCounts[$key] = $key === 'all'
                ? $followups->count()
                : $followups->where('stage_label', $label)->count();
        }

        return view('lead.followups', compact('followups', 'tabs', 'badgeColors', 'tabCounts'));
    }

    private function trainingLeadQuery(): Builder
    {
        return Lead::query()->training();
    }

    private function latestTrainingFollowups(): Collection
    {
        $stageMap = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'need_analysis' => 'Need Analysis',
            'branch_visited' => 'Branch Visited',
            'proposal_negotiation' => 'Proposal or Negotiation',
            'not_interesting' => 'Not Interesting',
            'registered' => 'Registered',
            'enroll' => 'Enrolled',
        ];

        return LeadFollowup::with(['lead.program', 'lead.campus'])
            ->whereHas('lead', fn (Builder $leadQuery) => $leadQuery->training())
            ->latest()
            ->get()
            ->unique('lead_id')
            ->values()
            ->map(function (LeadFollowup $followup) use ($stageMap) {
                $followup->stage_label = $stageMap[$followup->stage] ?? ucfirst(str_replace('_', ' ', $followup->stage));

                return $followup;
            });
    }

    private function buildWebLeadPrefill(WebLead $webLead): array
    {
        $remarks = array_filter([
            'Imported from ' . ($webLead->source_site ?: 'career.edu.pk') . ' (' . $webLead->source_label . ').',
            $webLead->interested_program ? 'Interested Program: ' . $webLead->interested_program : null,
            $webLead->preferred_campus ? 'Preferred Campus: ' . $webLead->preferred_campus : null,
            $webLead->message ? 'Website Message: ' . $webLead->message : null,
        ]);

        return [
            'name' => $webLead->full_name,
            'email' => $webLead->email,
            'phone' => $webLead->phone,
            'city' => $webLead->city,
            'program_id' => $this->resolveProgramIdFromWebLead($webLead),
            'campus_id' => $this->resolveCampusIdFromWebLead($webLead),
            'origin' => 'Website',
            'marketing_source' => 'Website',
            'details' => [
                'country' => $webLead->country ?: 'Pakistan',
                'area' => $webLead->area,
                'teaching_method' => $this->normalizeWebLeadTeachingMethod($webLead->teaching_method) ?: 'online',
                'gender' => $webLead->gender ?: 'male',
                'remarks' => implode(PHP_EOL, $remarks),
            ],
        ];
    }

    private function resolveProgramIdFromWebLead(WebLead $webLead): ?int
    {
        $payload = $webLead->payload ?? [];
        $programId = $payload['program_id'] ?? null;

        if (is_numeric($programId) && Program::query()->whereKey((int) $programId)->exists()) {
            return (int) $programId;
        }

        $candidate = trim((string) ($webLead->interested_program ?? ''));
        if ($candidate === '') {
            return null;
        }

        $needle = Str::lower($candidate);

        return Program::query()
            ->where(function (Builder $query) use ($needle) {
                $query->whereRaw('LOWER(code) = ?', [$needle])
                    ->orWhereRaw('LOWER(title) = ?', [$needle])
                    ->orWhereRaw('LOWER(name) = ?', [$needle]);
            })
            ->value('id');
    }

    private function resolveCampusIdFromWebLead(WebLead $webLead): ?int
    {
        $payload = $webLead->payload ?? [];
        $campusId = $payload['campus_id'] ?? null;

        if (is_numeric($campusId) && Campus::query()->whereKey((int) $campusId)->exists()) {
            return (int) $campusId;
        }

        $candidate = trim((string) ($webLead->preferred_campus ?? ''));
        if ($candidate === '') {
            return null;
        }

        $needle = Str::lower($candidate);

        return Campus::query()
            ->where(function (Builder $query) use ($needle) {
                $query->whereRaw('LOWER(code) = ?', [$needle])
                    ->orWhereRaw('LOWER(name) = ?', [$needle]);
            })
            ->value('id');
    }

    private function normalizeWebLeadTeachingMethod(?string $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return match (Str::lower(trim($value))) {
            'on-campus', 'on campus', 'physical' => 'campus',
            default => Str::lower(trim($value)),
        };
    }
}
