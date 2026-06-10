<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadTransfer;
use App\Models\Program;
use App\Models\User;
use App\Models\WebLead;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $todayOnly = $request->boolean('today');

        $leadQuery = $this->trainingLeadQuery()
            ->with([
                'program',
                'campus',
                'createdBy:id,name',
            ])
            ->withCount('followups')
            ->latest();

        if ($todayOnly) {
            $leadQuery->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
        }

        $leads = $leadQuery->get();

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

        return view('lead.all', compact('leads', 'tabs', 'badgeColors', 'tabCounts', 'todayOnly'));
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

        return view('lead.create', array_merge(
            $this->leadFormOptions(),
            compact('webLead', 'leadPrefill')
        ));
    }

    public function edit(Request $request, Lead $lead): View
    {
        $this->ensureLeadCampusAccess($lead);
        abort_unless($request->user()?->isAdmin(), 403);

        return view('lead.create', array_merge(
            $this->leadFormOptions(),
            [
                'lead' => $lead,
                'webLead' => null,
                'leadPrefill' => $this->buildLeadPrefillFromLead($lead),
                'formAction' => route('leads.update', $lead),
                'formMethod' => 'PUT',
                'formTitle' => 'Edit Lead',
                'formSubmitLabel' => 'Update Lead',
                'cancelUrl' => route('leads.show', $lead),
                'leadTypeSelectEnabled' => false,
            ]
        ));
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

        $validated = $request->validate(
            $this->leadStoreRules((string) $request->input('type')),
            $this->leadStoreMessages(),
            $this->leadStoreAttributes()
        );

        try {
            $details = $validated['details'] ?? [];
            $initialProbability = $details['probability'] ?? null;
            $initialNextAt = $this->normalizeFollowupDateTime($details['next_followup_at'] ?? null);
            $initialStage = $this->resolveInitialFollowupStage($validated['origin'] ?? null);

            if ($initialNextAt) {
                $details['next_followup_at'] = $initialNextAt->format('Y-m-d\TH:i');
            } else {
                unset($details['next_followup_at']);
            }

            $lead = Lead::create([
                'campus_id' => $validated['campus_id'] ?? null,
                'program_id' => $validated['program_id'] ?? null,
                'assigned_user_id' => $validated['assigned_user_id'] ?? null,
                'created_by' => $request->user()?->id,
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
                'next_action_date' => $initialNextAt,
                'stage' => $initialStage,
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

            return Redirect::route($this->leadFollowupsRouteName($lead->type))->with('status', 'Lead created with initial follow-up.');
        } catch (Throwable $e) {
            report($e);
            return Redirect::back()
                ->withInput()
                ->with('error', 'Unable to save the lead right now. Please try again.');
        }
    }

    public function update(Request $request, Lead $lead): RedirectResponse
    {
        $this->ensureLeadCampusAccess($lead);
        abort_unless($request->user()?->isAdmin(), 403);

        $request->merge([
            'type' => $lead->type,
        ]);

        $validated = $request->validate(
            $this->leadStoreRules($lead->type, $lead),
            $this->leadStoreMessages(),
            $this->leadStoreAttributes()
        );

        $lead->update([
            'campus_id' => $validated['campus_id'] ?? null,
            'program_id' => $validated['program_id'] ?? null,
            'assigned_user_id' => $validated['assigned_user_id'] ?? $lead->assigned_user_id,
            'type' => $lead->type,
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'origin' => $validated['origin'] ?? null,
            'marketing_source' => $validated['marketing_source'] ?? null,
            'details' => $validated['details'] ?? [],
        ]);

        return Redirect::route('leads.show', $lead)->with('status', 'Lead updated successfully.');
    }

    public function markNotInterested(Request $request, Lead $lead): RedirectResponse
    {
        $this->ensureLeadCampusAccess($lead);

        if (in_array($lead->status, $this->closedFollowupStatuses($lead->type), true)) {
            return Redirect::back()->with('error', 'This lead is already ' . str_replace('_', ' ', $lead->status) . '.');
        }

        $lead->update([
            'status' => 'not_interesting',
        ]);

        $this->syncLeadNextFollowupAt($lead, null);

        LeadFollowup::create([
            'lead_id' => $lead->id,
            'campus_id' => $this->resolveLeadCampusScopeId($lead),
            'user_id' => $request->user()?->id,
            'method' => null,
            'probability' => null,
            'note' => 'Lead marked as not interested from the actions menu.',
            'next_action_date' => null,
            'stage' => 'not_interesting',
            'lead_status' => 'not_interesting',
        ]);

        return Redirect::back()->with('status', 'Lead marked as not interested.');
    }

    public function addFollowup(Request $request, Lead $lead): RedirectResponse
    {
        $this->ensureLeadCampusAccess($lead);

        if (in_array($lead->status, $this->closedFollowupStatuses($lead->type), true)) {
            return Redirect::back()->with('error', 'This lead is already ' . str_replace('_', ' ', $lead->status) . '; no further follow-ups allowed.');
        }

        $request->merge([
            'stage' => $this->normalizeFollowupStage($lead->type, $request->input('stage')),
        ]);

        $currentStage = $this->resolveCurrentStage($lead);
        $canUpdateLeadProfile = $currentStage === 'new';
        $usesTrainingConversionFlow = $this->usesTrainingConversionFlow($lead->type);
        $allowedStages = array_keys($this->followupStageConfig($lead->type)['stageMap']);
        $selectedStage = (string) $request->input('stage');
        $usesMinimalFields = in_array($selectedStage, ['registered', 'not_interesting', 'enroll'], true);

        $rules = [
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'method' => ['required', 'string', 'max:50'],
            'probability' => $usesMinimalFields
                ? ['nullable', 'integer', 'min:1', 'max:100']
                : ['required', 'integer', 'min:1', 'max:100'],
            'note' => $selectedStage === 'not_interesting'
                ? ['required', 'string']
                : ['nullable', 'string'],
            'next_action_date' => ['nullable', 'date'],
            'stage' => ['required', Rule::in($allowedStages)],
        ];

        if ($canUpdateLeadProfile) {
            $rules['email'] = ['nullable', 'email', 'max:255'];
            $rules['lead_details'] = ['nullable', 'array'];
            $rules['lead_details.area'] = ['nullable', 'string', 'max:255'];
            $rules['lead_details.gender'] = ['nullable', Rule::in(['male', 'female', 'other'])];
        }

        $validated = $request->validate($rules, [
            'probability.min' => 'The probability must be greater than 0%.',
        ], [
            'method' => 'follow-up method',
            'note' => 'remarks',
        ]);

        if (! $usesTrainingConversionFlow && $validated['stage'] === 'registered') {
            throw ValidationException::withMessages([
                'stage' => [
                    'Use the coworking registration form to register this lead.',
                ],
            ]);
        }

        if ($usesTrainingConversionFlow && in_array($validated['stage'], ['registered', 'enroll'], true)) {
            throw ValidationException::withMessages([
                'stage' => [
                    $validated['stage'] === 'registered'
                        ? 'Use the registration form to register this lead.'
                        : 'Use the admission form to enroll this lead.',
                ],
            ]);
        }

        if ($canUpdateLeadProfile) {
            $this->syncLeadProfileFromFollowup($lead, $validated);
        }

        $followupCampusId = $validated['campus_id'] ?? $lead->campus_id;

        if (! $followupCampusId && $lead->type === 'coworking') {
            $followupCampusId = $this->resolveCoworkingCampus($lead)?->id;
        }

        $nextActionAt = $this->normalizeFollowupDateTime($validated['next_action_date'] ?? null);
        $leadStatusAfterFollowup = $this->resolveLeadStatusForFollowupStage($lead, $validated['stage']);

        LeadFollowup::create([
            'lead_id' => $lead->id,
            'campus_id' => $followupCampusId,
            'user_id' => $request->user()?->id,
            'method' => $validated['method'] ?? null,
            'probability' => $validated['probability'] ?? null,
            'note' => $validated['note'] ?? null,
            'next_action_date' => $nextActionAt,
            'stage' => $validated['stage'],
            'lead_status' => $leadStatusAfterFollowup,
        ]);

        $this->syncLeadNextFollowupAt(
            $lead,
            in_array($validated['stage'], ['registered', 'not_interesting', 'enroll'], true) ? null : $nextActionAt
        );

        if (in_array($validated['stage'], ['registered', 'not_interesting', 'enroll'], true)) {
            $lead->update([
                'status' => $leadStatusAfterFollowup,
            ]);
        }

        return Redirect::back()->with('status', 'Follow-up added.');
    }

    public function show(Lead $lead): View
    {
        $this->ensureLeadCampusAccess($lead);

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

        $isCoworkingLead = $lead->type === 'coworking';
        $usesTrainingConversionFlow = $this->usesTrainingConversionFlow($lead->type);
        $stages = $this->followupStageConfig($lead->type)['stageMap'];
        $campuses = Campus::orderBy('name')->get();
        $resolvedCoworkingCampus = $isCoworkingLead
            ? $this->resolveCoworkingCampus($lead, $campuses)
            : null;
        $leadLocationCode = $isCoworkingLead
            ? $this->resolveCoworkingBranchCode($lead, $campuses)
            : $lead->campus?->code;
        $leadLocationName = $isCoworkingLead
            ? ($resolvedCoworkingCampus?->name ?? $resolvedCoworkingCampus?->title)
            : $lead->campus?->name;
        $defaultFollowupCampusId = $isCoworkingLead
            ? ($resolvedCoworkingCampus?->id ?? $lead->campus_id)
            : $lead->campus_id;

        if ($isCoworkingLead) {
            $lead->setRelation('program', new Program([
                'title' => data_get($lead->details, 'space_required'),
                'name' => data_get($lead->details, 'space_required'),
            ]));

            if (! $lead->campus && $resolvedCoworkingCampus) {
                $lead->setRelation('campus', $resolvedCoworkingCampus);
            }
        }

        $followups = $lead->followups
            ->sortByDesc('created_at')
            ->values()
            ->map(function (LeadFollowup $followup) use ($isCoworkingLead, $leadLocationCode, $resolvedCoworkingCampus) {
                $followup->stage = $this->normalizeFollowupStage($followup->lead?->type, $followup->stage);

                if ($isCoworkingLead && ! $followup->campus && $resolvedCoworkingCampus) {
                    $followup->setRelation('campus', $resolvedCoworkingCampus);
                }

                $followup->location_code = $isCoworkingLead
                    ? ($followup->campus?->code ?? $leadLocationCode)
                    : ($followup->campus?->code ?? $followup->campus?->name);

                return $followup;
            });

        $currentStage = $this->normalizeFollowupStage($lead->type, $followups->first()->stage ?? 'new');
        $latestFollowup = $followups->first();
        $nextFollowup = $followups->firstWhere('next_action_date', '!=', null);
        $isFollowupClosed = in_array($lead->status, $this->closedFollowupStatuses($lead->type), true);

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
            'defaultFollowupCampusId' => $defaultFollowupCampusId,
            'isCoworkingLead' => $isCoworkingLead,
            'isFollowupClosed' => $isFollowupClosed,
            'usesTrainingConversionFlow' => $usesTrainingConversionFlow,
            'leadLocationCode' => $leadLocationCode,
            'leadLocationName' => $leadLocationName,
            'transfers' => $lead->transfers()->latest()->get(),
        ]);
    }

    public function transferForm(Lead $lead): View
    {
        $this->ensureLeadCampusAccess($lead);

        if (in_array($lead->status, $this->closedFollowupStatuses($lead->type), true)) {
            abort(403, 'Closed leads cannot be transferred.');
        }
        $campuses = Campus::orderBy('name')->get();
        return view('lead.transfer', compact('lead', 'campuses'));
    }

    public function transferStore(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $this->ensureLeadCampusAccess($lead);

        if (in_array($lead->status, $this->closedFollowupStatuses($lead->type), true)) {
            $message = 'Closed leads cannot be transferred.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => ['transfer' => [$message]],
                ], 422);
            }

            return Redirect::back()->withErrors(['transfer' => $message]);
        }
        $validated = $request->validate([
            'to_campus_id' => ['required', 'exists:campuses,id', 'different:from_campus_id'],
            'reason' => ['nullable', 'string'],
        ]);

        if ($lead->campus_id == $validated['to_campus_id']) {
            $message = 'Lead is already in the selected campus.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => ['to_campus_id' => [$message]],
                ], 422);
            }

            return Redirect::back()->withErrors(['to_campus_id' => $message]);
        }

        LeadTransfer::create([
            'lead_id' => $lead->id,
            'from_campus_id' => $lead->campus_id,
            'to_campus_id' => $validated['to_campus_id'],
            'transferred_by' => $request->user()?->id,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        $message = 'Transfer request submitted for approval.';

        if ($request->expectsJson()) {
            return response()->json(['status' => $message]);
        }

        return Redirect::route('leads.transfer')->with('status', $message);
    }

    public function approveTransfer(Request $request, LeadTransfer $transfer): RedirectResponse
    {
        $transfer->loadMissing('lead.campus');
        $this->ensureLeadCampusAccess($transfer->lead);

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
                ->whereHas('lead', fn (Builder $leadQuery) => $this->scopeLeadQueryToCurrentCampus($leadQuery->training()))
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
        return $this->renderFollowupsModule('training');
    }

    public function coworkingFollowups(): View
    {
        return $this->renderFollowupsModule('coworking');
    }

    private function trainingLeadQuery(): Builder
    {
        return $this->scopeLeadQueryToCurrentCampus(Lead::query()->training());
    }

    private function coworkingLeadQuery(): Builder
    {
        return Lead::query()->coworking();
    }

    private function latestTrainingFollowups(): Collection
    {
        return $this->latestFollowupsByType('training');
    }

    private function latestFollowupsByType(string $type): Collection
    {
        $stageMap = $this->followupStageConfig($type)['stageMap'];

        $campusDirectory = $type === 'coworking'
            ? Campus::query()->get(['id', 'code', 'city', 'city_abbr', 'name', 'title'])
            : collect();

        return LeadFollowup::with([
                'lead' => fn ($query) => $query->withCount('followups'),
                'lead.program',
                'lead.campus',
                'lead.coworkingRegistration',
                'user:id,name',
            ])
            ->whereHas('lead', function (Builder $leadQuery) use ($type) {
                if ($type === 'coworking') {
                    $leadQuery->coworking();
                } else {
                    $this->scopeLeadQueryToCurrentCampus($leadQuery->training());
                }
            })
            ->latest()
            ->get()
            ->unique('lead_id')
            ->values()
            ->map(function (LeadFollowup $followup) use ($stageMap, $campusDirectory, $type) {
                $followup->stage = $this->normalizeFollowupStage($type, $followup->stage);
                $followup->stage_label = $stageMap[$followup->stage] ?? ucfirst(str_replace('_', ' ', $followup->stage));
                $followup->followups_count = (int) ($followup->lead?->followups_count ?? 0);
                $followup->last_follower_name = trim((string) ($followup->user?->name ?? '')) ?: 'System';

                if ($type === 'coworking') {
                    $followup->branch_code = $this->resolveCoworkingBranchCode($followup->lead, $campusDirectory);
                }

                return $followup;
            });
    }

    private function resolveCoworkingBranchCode(?Lead $lead, Collection $campuses): ?string
    {
        if (! $lead) {
            return null;
        }

        $preferredLocation = trim((string) data_get($lead->details, 'preferred_location'));
        if ($preferredLocation !== '') {
            return $this->resolveCoworkingCampus($lead, $campuses)?->code ?: $preferredLocation;
        }

        return $this->resolveCoworkingCampus($lead, $campuses)?->code;
    }

    private function resolveCoworkingCampus(?Lead $lead, ?Collection $campuses = null): ?Campus
    {
        if (! $lead) {
            return null;
        }

        $directory = $campuses ?? Campus::query()->get(['id', 'code', 'city', 'city_abbr', 'name', 'title']);
        $candidates = array_filter([
            data_get($lead->details, 'preferred_location'),
            $lead->campus?->code,
            $lead->campus?->name,
            $lead->city,
            data_get($lead->details, 'area'),
        ], fn ($value) => trim((string) $value) !== '');

        foreach ($candidates as $candidate) {
            $needle = Str::lower(trim((string) $candidate));

            $campus = $directory->first(function (Campus $campus) use ($needle) {
                return in_array($needle, array_filter([
                    Str::lower((string) $campus->code),
                    Str::lower((string) $campus->city),
                    Str::lower((string) $campus->city_abbr),
                    Str::lower((string) $campus->name),
                    Str::lower((string) $campus->title),
                ], fn ($value) => $value !== ''), true);
            });

            if (filled($campus?->code)) {
                return $campus;
            }
        }

        return $lead->campus;
    }

    private function scopeLeadQueryToCurrentCampus(Builder $query): Builder
    {
        $campusScopeId = $this->currentUserCampusScopeId();

        return $query->when(
            $campusScopeId,
            fn (Builder $builder, int $campusId) => $builder->where('campus_id', $campusId)
        );
    }

    private function ensureLeadCampusAccess(?Lead $lead): void
    {
        if (! $lead) {
            abort(404);
        }

        $campusScopeId = $this->currentUserCampusScopeId();
        $leadCampusId = $this->resolveLeadCampusScopeId($lead);

        if ($campusScopeId && $leadCampusId && $leadCampusId !== $campusScopeId) {
            abort(403, 'You are not allowed to access leads from another campus.');
        }
    }

    private function resolveLeadCampusScopeId(Lead $lead): ?int
    {
        $campusId = (int) ($lead->campus_id ?? 0);
        if ($campusId > 0) {
            return $campusId;
        }

        $resolvedCampusId = (int) ($this->resolveCoworkingCampus($lead)?->id ?? 0);

        return $resolvedCampusId > 0 ? $resolvedCampusId : null;
    }

    private function currentUserCampusScopeId(): ?int
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user || $user->isAdmin()) {
            return null;
        }

        $campusId = (int) ($user->campus_id ?? 0);

        return $campusId > 0 ? $campusId : null;
    }

    private function renderFollowupsModule(string $type): View
    {
        $followups = $this->latestFollowupsByType($type);
        $stageConfig = $this->followupStageConfig($type);
        $tabs = $stageConfig['tabs'];
        $badgeColors = $stageConfig['badgeColors'];

        $tabCounts = [];
        foreach ($tabs as $key => $label) {
            $tabCounts[$key] = $key === 'all'
                ? $followups->count()
                : $followups->where('stage_label', $label)->count();
        }

        $pageTitle = $type === 'coworking'
            ? 'Coworking Space Follow-ups'
            : 'Training Lead Follow-ups';

        $moduleTitle = $type === 'coworking'
            ? 'Coworking Space'
            : 'Training Leads';

        $interestHeading = $type === 'coworking'
            ? 'Space Type'
            : 'Interested Course';

        return view('lead.followups', compact(
            'followups',
            'tabs',
            'badgeColors',
            'tabCounts',
            'pageTitle',
            'moduleTitle',
            'interestHeading',
            'type'
        ));
    }

    private function followupStageConfig(string $type): array
    {
        if ($type === 'coworking') {
            return [
                'stageMap' => [
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'need_analysis' => 'Need Analysis',
                    'branch_visited' => 'Branch Visited',
                    'proposal_negotiation' => 'Proposal & Negotiation',
                    'not_interesting' => 'Not Interesting',
                    'registered' => 'Registered',
                ],
                'tabs' => [
                    'all' => 'All',
                    'New' => 'New',
                    'Contacted' => 'Contacted',
                    'Need Analysis' => 'Need Analysis',
                    'Branch Visited' => 'Branch Visited',
                    'Proposal & Negotiation' => 'Proposal & Negotiation',
                    'Registered' => 'Registered',
                    'Not Interesting' => 'Not Interesting',
                ],
                'badgeColors' => [
                    'all' => 'badge-secondary',
                    'New' => 'badge-primary',
                    'Contacted' => 'badge-success',
                    'Need Analysis' => 'badge-warning',
                    'Branch Visited' => 'badge-secondary',
                    'Proposal & Negotiation' => 'badge-info',
                    'Registered' => 'badge-success',
                    'Not Interesting' => 'badge-warning',
                ],
            ];
        }

        return [
            'stageMap' => [
                'new' => 'New',
                'contacted' => 'Contacted',
                'need_analysis' => 'Need Analysis',
                'branch_visited' => 'Branch Visited',
                'proposal_negotiation' => 'Proposal & Negotiation',
                'not_interesting' => 'Not Interesting',
                'registered' => 'Registered',
                'enroll' => 'Enrolled',
            ],
            'tabs' => [
                'all' => 'All',
                'New' => 'New',
                'Contacted' => 'Contacted',
                'Need Analysis' => 'Need Analysis',
                'Branch Visited' => 'Branch Visited',
                'Proposal & Negotiation' => 'Proposal & Negotiation',
                'Not Interesting' => 'Not Interesting',
                'Registered' => 'Registered',
                'Enrolled' => 'Enrolled',
            ],
            'badgeColors' => [
                'all' => 'badge-secondary',
                'New' => 'badge-primary',
                'Contacted' => 'badge-success',
                'Need Analysis' => 'badge-warning',
                'Branch Visited' => 'badge-secondary',
                'Proposal & Negotiation' => 'badge-info',
                'Not Interesting' => 'badge-warning',
                'Registered' => 'badge-success',
                'Enrolled' => 'badge-success',
            ],
        ];
    }

    private function leadFollowupsRouteName(?string $type): string
    {
        return match ($type) {
            'coworking' => 'leads.coworking.followups',
            default => 'leads.followups',
        };
    }

    private function usesTrainingConversionFlow(?string $type): bool
    {
        return $type !== 'coworking';
    }

    private function closedFollowupStatuses(?string $type): array
    {
        return $this->usesTrainingConversionFlow($type)
            ? ['registered', 'not_interesting', 'enrolled']
            : ['registered', 'not_interesting', 'enrolled'];
    }

    private function resolveLeadStatusForFollowupStage(Lead $lead, string $stage): string
    {
        return match ($stage) {
            'registered' => 'registered',
            'enroll' => 'enrolled',
            'not_interesting' => 'not_interesting',
            default => (string) ($lead->status ?? 'pending'),
        };
    }

    private function normalizeFollowupStage(?string $type, ?string $stage): string
    {
        $normalizedStage = trim((string) $stage);

        if ($type === 'coworking' && $normalizedStage === 'enroll') {
            return 'registered';
        }

        return $normalizedStage !== '' ? $normalizedStage : 'new';
    }

    private function resolveInitialFollowupStage(?string $origin): string
    {
        $normalizedOrigin = trim((string) preg_replace('/[^a-z0-9]+/i', '_', (string) $origin), '_');
        $normalizedOrigin = Str::lower($normalizedOrigin);

        if (in_array($normalizedOrigin, ['website', 'web_site'], true)) {
            return 'new';
        }

        if (in_array($normalizedOrigin, ['walk_in', 'walkin'], true)) {
            return 'branch_visited';
        }

        return 'contacted';
    }

    private function resolveCurrentStage(Lead $lead): string
    {
        return $lead->followups()->latest('id')->value('stage') ?? 'new';
    }

    private function syncLeadProfileFromFollowup(Lead $lead, array $validated): void
    {
        $leadUpdates = [];
        $details = $lead->details ?? [];
        $detailUpdates = $validated['lead_details'] ?? [];

        if (filled($validated['email'] ?? null)) {
            $leadUpdates['email'] = $validated['email'];
        }

        if (filled($detailUpdates['area'] ?? null)) {
            $details['area'] = $detailUpdates['area'];
        }

        if (filled($detailUpdates['gender'] ?? null)) {
            $details['gender'] = $detailUpdates['gender'];
        }

        if ($details !== ($lead->details ?? [])) {
            $leadUpdates['details'] = $details;
        }

        if ($leadUpdates !== []) {
            $lead->update($leadUpdates);
        }
    }

    private function syncLeadNextFollowupAt(Lead $lead, ?Carbon $nextActionAt): void
    {
        $details = $lead->details ?? [];

        if ($nextActionAt) {
            $details['next_followup_at'] = $nextActionAt->format('Y-m-d\TH:i');
        } else {
            unset($details['next_followup_at']);
        }

        if ($details !== ($lead->details ?? [])) {
            $lead->update(['details' => $details]);
        }
    }

    private function normalizeFollowupDateTime(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

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

        return Carbon::parse($stringValue);
    }

    private function leadFormOptions(): array
    {
        return [
            'campuses' => Campus::query()
                ->orderBy('name')
                ->get(),
            'programs' => Program::orderBy('title')->get(),
            'origins' => ['Walk-In', 'WhatsApp Business', 'Facebook', 'Google Business', 'Website', 'Instagram', 'LinkedIn', 'Referral', 'Other'],
            'marketingSources' => ['Alumni', 'Career team', 'Event/ Expo', 'Email', 'Facebook', 'Google', 'Instagram', 'LinkedIn', 'Referral', 'Website', 'Other'],
        ];
    }

    private function buildLeadPrefillFromLead(Lead $lead): array
    {
        return [
            'type' => $lead->type,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'city' => $lead->city,
            'origin' => $lead->origin,
            'marketing_source' => $lead->marketing_source,
            'campus_id' => $lead->campus_id,
            'program_id' => $lead->program_id,
            'details' => $lead->details ?? [],
        ];
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

    private function leadStoreRules(string $type, ?Lead $lead = null): array
    {
        $rules = [
            'web_lead_id' => ['nullable', 'exists:web_leads,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', Rule::in(['training', 'certification', 'coworking', 'study_abroad'])],
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^03\d{9}$/', Rule::unique('leads', 'phone')->ignore($lead?->id)],
            'city' => ['nullable', 'string', 'max:255'],
            'origin' => ['required', 'string', 'max:255'],
            'marketing_source' => ['required', 'string', 'max:255'],
            'campus_id' => ['nullable', 'integer', 'exists:campuses,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'details' => ['nullable', 'array'],
            'details.country' => ['nullable', 'string', 'max:255'],
            'details.area' => ['nullable', 'string', 'min:2', 'max:255'],
            'details.gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'details.next_followup_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'details.probability' => ['nullable', 'integer', 'min:1', 'max:100'],
            'details.remarks' => ['nullable', 'string', 'min:5', 'max:1000'],
            'details.teaching_method' => ['nullable', Rule::in(['campus', 'online', 'hybrid'])],
            'details.organization' => ['nullable', 'string', 'max:255'],
            'details.certification_title' => ['nullable', 'string', 'max:255'],
            'details.exam_code' => ['nullable', 'string', 'max:100'],
            'details.business_name' => ['nullable', 'string', 'max:255'],
            'details.person_count' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'details.space_required' => ['nullable', Rule::in(['Dedicated Desk', 'Shared Office', 'Private Office', 'Studio Space', 'Meeting Room', 'Event Hall', 'Virtual Office'])],
            'details.preferred_location' => ['nullable', 'string', 'max:255'],
            'details.expected_starting_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'details.additional_amenities' => ['nullable', 'string', 'max:1000'],
            'details.current_education' => ['nullable', 'string', 'max:255'],
            'details.preferred_study_program' => ['nullable', 'string', 'max:255'],
            'details.preferred_country' => ['nullable', 'string', 'max:255'],
            'details.preferred_university' => ['nullable', 'string', 'max:255'],
        ];

        return match ($type) {
            'training' => array_merge($rules, [
                'program_id' => ['required', 'integer', 'exists:programs,id'],
                'email' => ['required', 'email', 'max:255'],
                'campus_id' => ['required', 'integer', 'exists:campuses,id'],
                'details.area' => ['required', 'string', 'min:2', 'max:255'],
                'details.next_followup_at' => ['required', 'date_format:Y-m-d\TH:i'],
                'details.remarks' => ['required', 'string', 'min:5', 'max:1000'],
            ]),
            'certification' => array_merge($rules, [
                'city' => ['required', 'string', 'max:255'],
                'details.country' => ['required', 'string', 'max:255'],
                'details.gender' => ['required', Rule::in(['male', 'female', 'other'])],
                'details.teaching_method' => ['required', Rule::in(['campus', 'online', 'hybrid'])],
                'details.organization' => ['required', 'string', 'max:255'],
                'details.certification_title' => ['required', 'string', 'max:255'],
                'details.next_followup_at' => ['required', 'date_format:Y-m-d\TH:i'],
                'details.probability' => ['required', 'integer', 'min:1', 'max:100'],
                'details.remarks' => ['required', 'string', 'min:5', 'max:1000'],
            ]),
            'coworking' => array_merge($rules, [
                'city' => ['required', 'string', 'max:255'],
                'details.country' => ['required', 'string', 'max:255'],
                'details.gender' => ['required', Rule::in(['male', 'female', 'other'])],
                'details.business_name' => ['required', 'string', 'max:255'],
                'details.person_count' => ['required', 'integer', 'min:1', 'max:5000'],
                'details.space_required' => ['required', Rule::in(['Dedicated Desk', 'Shared Office', 'Private Office', 'Studio Space', 'Meeting Room', 'Event Hall', 'Virtual Office'])],
                'details.next_followup_at' => ['required', 'date_format:Y-m-d\TH:i'],
                'details.probability' => ['required', 'integer', 'min:1', 'max:100'],
                'details.remarks' => ['required', 'string', 'min:5', 'max:1000'],
            ]),
            'study_abroad' => array_merge($rules, [
                'city' => ['required', 'string', 'max:255'],
                'details.country' => ['required', 'string', 'max:255'],
                'details.gender' => ['required', Rule::in(['male', 'female', 'other'])],
                'details.current_education' => ['required', 'string', 'max:255'],
                'details.preferred_study_program' => ['required', 'string', 'max:255'],
                'details.preferred_country' => ['required', 'string', 'max:255'],
                'details.next_followup_at' => ['required', 'date_format:Y-m-d\TH:i'],
                'details.probability' => ['required', 'integer', 'min:1', 'max:100'],
                'details.remarks' => ['required', 'string', 'min:5', 'max:1000'],
            ]),
            default => $rules,
        };
    }

    private function leadStoreMessages(): array
    {
        return [
            'phone.regex' => 'The phone number must be 11 digits, contain digits only, and start with 03.',
            'details.probability.min' => 'The probability must be greater than 0%.',
            'probability.min' => 'The probability must be greater than 0%.',
        ];
    }

    private function leadStoreAttributes(): array
    {
        return [
            'program_id' => 'course interested',
            'campus_id' => 'preferred campus',
            'details.country' => 'country',
            'details.area' => 'area',
            'details.gender' => 'gender',
            'details.teaching_method' => 'teaching method',
            'details.next_followup_at' => 'next follow up',
            'details.probability' => 'probability',
            'details.remarks' => 'remarks',
            'details.organization' => 'organization/vendor',
            'details.certification_title' => 'certification title',
            'details.exam_code' => 'exam code',
            'details.business_name' => 'business/team name',
            'details.person_count' => 'number of persons',
            'details.space_required' => 'space required',
            'details.preferred_location' => 'preferred branch',
            'details.expected_starting_at' => 'expected starting date',
            'details.additional_amenities' => 'additional amenities',
            'details.current_education' => 'current education',
            'details.preferred_study_program' => 'preferred study program',
            'details.preferred_country' => 'preferred country',
            'details.preferred_university' => 'preferred university',
        ];
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
