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
use Illuminate\Support\Facades\DB;
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
        return $this->renderLeadIndex('training', $request);
    }

    public function certificationIndex(Request $request): View
    {
        return $this->renderLeadIndex('certification', $request);
    }

    public function studyAbroadIndex(Request $request): View
    {
        return $this->renderLeadIndex('study_abroad', $request);
    }

    public function coworkingIndex(Request $request): View
    {
        return $this->renderLeadIndex('coworking', $request);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->ensureLeadCreatePermission($request);

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
        $this->ensureLeadCreatePermission($request);

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
            $lead = DB::transaction(function () use ($request, $validated, $webLead) {
                $details = $validated['details'] ?? [];
                $initialProbability = $details['probability'] ?? null;
                $initialNextAt = $this->normalizeFollowupDateTime($details['next_followup_at'] ?? null);
                $initialStage = $this->resolveInitialFollowupStage($validated['origin'] ?? null);
                $initialMethod = $this->resolveInitialFollowupMethod($validated['origin'] ?? null);

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
                    'note' => trim((string) ($details['remarks'] ?? '')) !== ''
                        ? trim((string) $details['remarks'])
                        : 'Initial follow-up created automatically.',
                    'method' => $initialMethod,
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

                return $lead;
            });

            $statusMessage = 'Lead created with initial follow-up.';
            $followupPermissions = $lead->type === 'coworking'
                ? ['lead.coworking.view']
                : ['lead.followup.view'];

            if ($request->user()?->hasAnyPermission($followupPermissions) ?? false) {
                return Redirect::route($this->leadFollowupsRouteName($lead->type))->with('status', $statusMessage);
            }

            if ($request->user()?->hasAnyPermission(['lead.view']) ?? false) {
                return Redirect::route('leads.show', $lead)->with('status', $statusMessage);
            }

            if ($webLead && ($request->user()?->hasAnyPermission(['web-lead.view']) ?? false)) {
                return Redirect::route('web-leads.show', $webLead->fresh())->with('status', $statusMessage);
            }

            return Redirect::route('dashboard')->with('status', $statusMessage);
        } catch (Throwable $e) {
            logger()->error('Lead create failed.', [
                'user_id' => $request->user()?->id,
                'type' => $request->input('type'),
                'phone' => $request->input('phone'),
                'campus_id' => $request->input('campus_id'),
                'program_id' => $request->input('program_id'),
                'web_lead_id' => $request->input('web_lead_id'),
                'exception_class' => $e::class,
                'exception_message' => $e->getMessage(),
            ]);
            report($e);

            $errorMessage = app()->isLocal()
                ? 'Unable to save the lead: ' . $e->getMessage()
                : 'Unable to save the lead right now. Please try again.';

            return Redirect::back()
                ->withInput()
                ->with('error', $errorMessage);
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

        $mergedDetails = $this->mergeLeadDetails($lead->details, $validated['details'] ?? []);

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
            'details' => $mergedDetails,
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
            'campus_id' => $this->resolveFollowupCampusId($lead),
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

    public function addFollowup(Request $request, Lead $lead): RedirectResponse|JsonResponse
    {
        $this->ensureLeadCampusAccess($lead);

        if (in_array($lead->status, $this->closedFollowupStatuses($lead->type), true)) {
            $message = 'This lead is already ' . str_replace('_', ' ', $lead->status) . '; no further follow-ups allowed.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => ['stage' => [$message]],
                ], 422);
            }

            return Redirect::back()->withErrors(['stage' => $message]);
        }

        $request->merge([
            'stage' => $this->normalizeFollowupStage($lead->type, $request->input('stage')),
        ]);

        $currentStage = $this->resolveCurrentStage($lead);
        $canUpdateLeadProfile = $currentStage === 'new';
        $usesTrainingConversionFlow = $this->usesTrainingConversionFlow($lead->type);
        $supportsRegistration = $this->supportsRegistration($lead->type);
        $supportsAdmission = $this->supportsAdmission($lead->type);
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

        if ($lead->type === 'coworking' && $validated['stage'] === 'registered') {
            throw ValidationException::withMessages([
                'stage' => [
                    'Use the coworking registration form to register this lead.',
                ],
            ]);
        }

        if ($supportsRegistration && $usesTrainingConversionFlow && in_array($validated['stage'], ['registered', 'enroll'], true)) {
            throw ValidationException::withMessages([
                'stage' => [
                    $validated['stage'] === 'registered'
                        ? 'Use the registration form to register this lead.'
                        : 'Use the admission form to enroll this lead.',
                ],
            ]);
        }

        if (! $supportsAdmission && $validated['stage'] === 'enroll') {
            throw ValidationException::withMessages([
                'stage' => [
                    'The selected lead type does not support enrollment.',
                ],
            ]);
        }

        if ($canUpdateLeadProfile) {
            $this->syncLeadProfileFromFollowup($lead, $validated);
        }

        $isTerminalStage = in_array($validated['stage'], ['registered', 'not_interesting', 'enroll'], true);
        $followupCampusId = $this->resolvePermittedFollowupCampusId($lead, $validated['campus_id'] ?? null);
        $nextActionAt = $isTerminalStage
            ? null
            : $this->normalizeFollowupDateTime($validated['next_action_date'] ?? null);
        $leadStatusAfterFollowup = $this->resolveLeadStatusForFollowupStage($lead, $validated['stage']);

        DB::transaction(function () use ($followupCampusId, $lead, $leadStatusAfterFollowup, $nextActionAt, $request, $validated, $isTerminalStage): void {
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
                $isTerminalStage ? null : $nextActionAt
            );

            if ($isTerminalStage) {
                $lead->update([
                    'status' => $leadStatusAfterFollowup,
                ]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'Follow-up added.',
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
        $supportsRegistration = $this->supportsRegistration($lead->type);
        $supportsAdmission = $this->supportsAdmission($lead->type);
        $stages = $this->followupStageConfig($lead->type)['stageMap'];
        $campuses = $this->followupCampusOptions();
        $resolvedCoworkingCampus = $isCoworkingLead
            ? $this->resolveCoworkingCampus($lead, $campuses)
            : null;
        $leadLocationCode = $isCoworkingLead
            ? $this->resolveCoworkingBranchCode($lead, $campuses)
            : $lead->campus?->code;
        $leadLocationName = $isCoworkingLead
            ? ($resolvedCoworkingCampus?->name ?? $resolvedCoworkingCampus?->title)
            : $lead->campus?->name;
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
        $previousFollowupCampusId = $this->resolvePreviousFollowupCampusId($lead);
        $defaultFollowupCampusId = $this->resolvePermittedFollowupCampusId($lead);
        $isFollowupClosed = in_array($lead->status, $this->closedFollowupStatuses($lead->type), true);
        $interestHeading = $this->leadInterestHeading($lead->type);
        $interestValue = $this->leadInterestValue($lead);

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
            'previousFollowupCampusId' => $previousFollowupCampusId,
            'isCoworkingLead' => $isCoworkingLead,
            'isFollowupClosed' => $isFollowupClosed,
            'usesTrainingConversionFlow' => $usesTrainingConversionFlow,
            'supportsRegistration' => $supportsRegistration,
            'supportsAdmission' => $supportsAdmission,
            'leadLocationCode' => $leadLocationCode,
            'leadLocationName' => $leadLocationName,
            'interestHeading' => $interestHeading,
            'interestValue' => $interestValue,
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
        $type = $this->normalizeLeadType((string) $request->query('type', 'training'));
        $typeMeta = $this->leadTypeMeta($type);

        if ($request->ajax()) {
            $query = LeadTransfer::query()
                ->whereHas('lead', fn (Builder $leadQuery) => $this->applyLeadTypeScope($leadQuery, $type))
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

        return view('lead.transfers', [
            'type' => $type,
            'moduleTitle' => $typeMeta['moduleTitle'],
            'ajaxUrl' => route('leads.transfer', ['type' => $type]),
        ]);
    }

    public function followups(Request $request): View
    {
        return $this->renderFollowupsModule('training', $request);
    }

    public function certificationFollowups(Request $request): View
    {
        return $this->renderFollowupsModule('certification', $request);
    }

    public function studyAbroadFollowups(Request $request): View
    {
        return $this->renderFollowupsModule('study_abroad', $request);
    }

    public function coworkingFollowups(Request $request): View
    {
        return $this->renderFollowupsModule('coworking', $request);
    }

    public function followupSchedule(Request $request): View
    {
        return $this->renderFollowupScheduleModule('training', $request);
    }

    public function certificationFollowupSchedule(Request $request): View
    {
        return $this->renderFollowupScheduleModule('certification', $request);
    }

    public function studyAbroadFollowupSchedule(Request $request): View
    {
        return $this->renderFollowupScheduleModule('study_abroad', $request);
    }

    public function coworkingFollowupSchedule(Request $request): View
    {
        return $this->renderFollowupScheduleModule('coworking', $request);
    }

    private function leadQueryForType(string $type): Builder
    {
        return $this->applyLeadTypeScope(Lead::query(), $type);
    }

    private function baseFollowupLeadQuery(string $type): Builder
    {
        $latestFollowupIdSubquery = LeadFollowup::query()
            ->select('id')
            ->whereColumn('lead_id', 'leads.id')
            ->orderByDesc('id')
            ->limit(1);

        $latestFollowupAtSubquery = LeadFollowup::query()
            ->selectRaw('COALESCE(updated_at, created_at)')
            ->whereColumn('lead_id', 'leads.id')
            ->orderByRaw('COALESCE(updated_at, created_at) DESC')
            ->orderByDesc('id')
            ->limit(1);

        $latestNextActionDateSubquery = LeadFollowup::query()
            ->select('next_action_date')
            ->whereColumn('lead_id', 'leads.id')
            ->orderByDesc('id')
            ->limit(1);

        return $this->leadQueryForType($type)
            ->whereHas('followups')
            ->with([
                'latestFollowup.user:id,name',
                'latestFollowup.campus:id,code,name,title,city,city_abbr',
                'campus:id,code,name,title,city,city_abbr',
                'program:id,title,name',
                'coworkingRegistration:id,lead_id',
            ])
            ->select('leads.*')
            ->selectSub($latestFollowupIdSubquery, 'latest_followup_id')
            ->selectSub($latestFollowupAtSubquery, 'latest_followup_at')
            ->selectSub($latestNextActionDateSubquery, 'latest_next_action_date')
            ->withCount('followups');
    }

    /**
     * @return array<string, int>
     */
    private function latestFollowupStageCounts(string $type, ?string $search = null): array
    {
        $latestStageSubquery = LeadFollowup::query()
            ->select('stage')
            ->whereColumn('lead_id', 'leads.id')
            ->orderByDesc('id')
            ->limit(1);

        $stageQuery = $this->leadQueryForType($type)
            ->whereHas('followups')
            ->selectSub($latestStageSubquery, 'latest_stage');

        if ($search !== null) {
            $this->applyFollowupSearch($stageQuery, $search, $type);
        }

        return DB::query()
            ->fromSub($stageQuery->toBase(), 'latest_lead_stages')
            ->selectRaw('latest_stage, COUNT(*) as aggregate')
            ->groupBy('latest_stage')
            ->pluck('aggregate', 'latest_stage')
            ->map(fn ($count) => (int) $count)
            ->all();
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

    private function resolveFollowupCampusId(Lead $lead, mixed $requestedCampusId = null): ?int
    {
        $candidateCampusIds = [];

        foreach ([
            $requestedCampusId,
            $this->resolvePreviousFollowupCampusId($lead),
            $lead->campus?->id,
            $lead->campus_id,
            $this->resolveCoworkingCampus($lead)?->id,
        ] as $candidateCampusId) {
            $campusId = (int) ($candidateCampusId ?? 0);

            if ($campusId <= 0 || in_array($campusId, $candidateCampusIds, true)) {
                continue;
            }

            $candidateCampusIds[] = $campusId;
        }

        if ($candidateCampusIds === []) {
            return null;
        }

        $validCampusIds = Campus::query()
            ->whereIn('id', $candidateCampusIds)
            ->pluck('id')
            ->map(fn ($campusId) => (int) $campusId)
            ->all();

        foreach ($candidateCampusIds as $campusId) {
            if (in_array($campusId, $validCampusIds, true)) {
                return $campusId;
            }
        }

        return null;
    }

    private function resolvePermittedFollowupCampusId(Lead $lead, mixed $requestedCampusId = null): ?int
    {
        return $this->resolveFollowupCampusId($lead, $requestedCampusId);
    }

    private function resolvePreviousFollowupCampusId(Lead $lead): ?int
    {
        if ($lead->relationLoaded('followups')) {
            $followup = $lead->followups
                ->sortByDesc(function (LeadFollowup $followup): int {
                    return $followup->created_at?->getTimestamp() ?? $followup->id;
                })
                ->first(function (LeadFollowup $followup): bool {
                    return (int) ($followup->campus_id ?? 0) > 0;
                });

            $campusId = (int) ($followup?->campus_id ?? 0);

            return $campusId > 0 ? $campusId : null;
        }

        $campusId = (int) LeadFollowup::query()
            ->where('lead_id', $lead->id)
            ->whereNotNull('campus_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('campus_id');

        return $campusId > 0 ? $campusId : null;
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

    private function renderLeadIndex(string $type, Request $request): View
    {
        $type = $this->normalizeLeadType($type);
        $todayOnly = $request->boolean('today');
        $typeMeta = $this->leadTypeMeta($type);
        $status = $this->normalizeLeadStatusFilter($request->query('status'), $type);
        $perPage = $this->resolvePerPage($request);
        $filters = $this->resolveLeadIndexFilters($request);
        $campuses = $this->leadIndexCampusOptions();
        $programs = $this->leadIndexProgramOptions($type, $filters['program_id'], $filters['campus_id']);

        $baseLeadQuery = $this->leadQueryForType($type);

        if ($todayOnly) {
            $baseLeadQuery->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
        }

        $this->applyLeadIndexFilters($baseLeadQuery, $filters);

        $tabs = $this->leadIndexTabs($type);
        $badgeColors = $this->leadIndexBadgeColors($type);
        $countsByStatus = (clone $baseLeadQuery)
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $totalLeads = (int) (clone $baseLeadQuery)->count();

        $leadQuery = (clone $baseLeadQuery)
            ->with([
                'program',
                'campus',
                'createdBy:id,name',
            ])
            ->withCount('followups')
            ->latest();

        if ($status !== null) {
            $leadQuery->where('status', $status);
        }

        $leads = $leadQuery
            ->paginate($perPage)
            ->withQueryString();

        $leads->setCollection(
            $leads->getCollection()->map(function (Lead $lead) {
                $lead->interest_summary = $this->leadInterestValue($lead);

                return $lead;
            })
        );

        $tabCounts = ['all' => $totalLeads];
        foreach (array_keys($tabs) as $key) {
            if ($key === 'all') {
                continue;
            }

            $tabCounts[$key] = (int) ($countsByStatus[$key] ?? 0);
        }

        return view('lead.all', [
            'leads' => $leads,
            'tabs' => $tabs,
            'badgeColors' => $badgeColors,
            'tabCounts' => $tabCounts,
            'selectedStatus' => $status ?? 'all',
            'perPage' => $perPage,
            'todayOnly' => $todayOnly,
            'type' => $type,
            'moduleTitle' => $typeMeta['moduleTitle'],
            'interestHeading' => $this->leadInterestHeading($type),
            'emptyStateMessage' => $typeMeta['emptyStateMessage'],
            'indexRoute' => route($typeMeta['indexRoute']),
            'filters' => $filters,
            'campuses' => $campuses,
            'programs' => $programs,
        ]);
    }

    private function resolveLeadIndexFilters(Request $request): array
    {
        $campusScopeId = $this->currentUserCampusScopeId();
        $requestedCampusId = (int) $request->query('campus_id', 0);
        $campusId = $campusScopeId ?: ($requestedCampusId > 0 ? $requestedCampusId : null);

        $requestedProgramId = (int) $request->query('program_id', 0);
        $programId = $requestedProgramId > 0 ? $requestedProgramId : null;

        $createdFrom = $this->normalizeLeadIndexDate($request->query('created_from'));
        $createdTo = $this->normalizeLeadIndexDate($request->query('created_to'));
        $search = trim((string) $request->query('search', ''));

        if ($createdFrom !== null && $createdTo !== null && $createdFrom > $createdTo) {
            [$createdFrom, $createdTo] = [$createdTo, $createdFrom];
        }

        return [
            'campus_id' => $campusId,
            'program_id' => $programId,
            'created_from' => $createdFrom,
            'created_to' => $createdTo,
            'search' => $search,
        ];
    }

    private function normalizeLeadIndexDate(mixed $value): ?string
    {
        $date = trim((string) $value);

        if ($date === '') {
            return null;
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    private function applyLeadIndexFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                $filters['campus_id'],
                fn (Builder $builder, int $campusId) => $builder->where('campus_id', $campusId)
            )
            ->when(
                $filters['program_id'],
                fn (Builder $builder, int $programId) => $builder->where('program_id', $programId)
            )
            ->when(
                $filters['created_from'],
                fn (Builder $builder, string $createdFrom) => $builder->whereDate('created_at', '>=', $createdFrom)
            )
            ->when(
                $filters['created_to'],
                fn (Builder $builder, string $createdTo) => $builder->whereDate('created_at', '<=', $createdTo)
            )
            ->when(
                $filters['search'] ?? '',
                function (Builder $builder, string $search): void {
                    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';

                    $builder->where(function (Builder $inner) use ($like): void {
                        $inner
                            ->where('name', 'like', $like)
                            ->orWhere('phone', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('city', 'like', $like)
                            ->orWhere('marketing_source', 'like', $like)
                            ->orWhere('origin', 'like', $like)
                            ->orWhere('status', 'like', $like)
                            ->orWhere('details', 'like', $like)
                            ->orWhereHas('program', function (Builder $programQuery) use ($like): void {
                                $programQuery
                                    ->where('title', 'like', $like)
                                    ->orWhere('name', 'like', $like)
                                    ->orWhere('code', 'like', $like);
                            })
                            ->orWhereHas('campus', function (Builder $campusQuery) use ($like): void {
                                $campusQuery
                                    ->where('code', 'like', $like)
                                    ->orWhere('name', 'like', $like)
                                    ->orWhere('title', 'like', $like)
                                    ->orWhere('city', 'like', $like);
                            })
                            ->orWhereHas('createdBy', function (Builder $userQuery) use ($like): void {
                                $userQuery->where('name', 'like', $like);
                            });
                    });
                }
            );
    }

    private function leadIndexCampusOptions(): Collection
    {
        return Campus::query()
            ->when(
                $this->currentUserCampusScopeId(),
                fn ($query, int $campusId) => $query->whereKey($campusId)
            )
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'title']);
    }

    private function followupCampusOptions(): Collection
    {
        return Campus::query()
            ->orderBy('code')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'title']);
    }

    private function leadIndexProgramOptions(string $type, ?int $selectedProgramId = null, ?int $campusId = null): Collection
    {
        $programIds = (clone $this->leadQueryForType($type))
            ->when(
                $campusId,
                fn (Builder $builder, int $resolvedCampusId) => $builder->where('campus_id', $resolvedCampusId)
            )
            ->whereNotNull('program_id')
            ->distinct()
            ->orderBy('program_id')
            ->pluck('program_id')
            ->map(fn ($programId) => (int) $programId)
            ->filter(fn (int $programId) => $programId > 0)
            ->values()
            ->all();

        if ($selectedProgramId && ! in_array($selectedProgramId, $programIds, true)) {
            $programIds[] = $selectedProgramId;
        }

        if ($programIds === []) {
            return collect();
        }

        return Program::query()
            ->whereIn('id', $programIds)
            ->orderBy('title')
            ->orderBy('name')
            ->get(['id', 'title', 'name']);
    }

    private function applyLeadTypeScope(Builder $query, string $type): Builder
    {
        $type = $this->normalizeLeadType($type);

        $query = match ($type) {
            'coworking' => $query->coworking(),
            'certification' => $query->certification(),
            'study_abroad' => $query->studyAbroad(),
            default => $query->training(),
        };

        if ($type === 'coworking') {
            return $query;
        }

        return $this->scopeLeadQueryToCurrentCampus($query);
    }

    private function normalizeLeadType(string $type): string
    {
        return in_array($type, ['training', 'coworking', 'certification', 'study_abroad'], true)
            ? $type
            : 'training';
    }

    /**
     * @return array{label: string, moduleTitle: string, indexPageTitle: string, followupsPageTitle: string, emptyStateMessage: string, indexRoute: string}
     */
    private function leadTypeMeta(string $type): array
    {
        return match ($this->normalizeLeadType($type)) {
            'coworking' => [
                'label' => 'Coworking Space',
                'moduleTitle' => 'Coworking Space',
                'indexPageTitle' => 'Coworking Space Leads',
                'followupsPageTitle' => 'Coworking Space Follow-ups',
                'emptyStateMessage' => 'No coworking leads found.',
                'indexRoute' => 'leads.coworking.index',
            ],
            'certification' => [
                'label' => 'Certification Exam',
                'moduleTitle' => 'Exam Leads',
                'indexPageTitle' => 'Exam Leads',
                'followupsPageTitle' => 'Exam Lead Follow-ups',
                'emptyStateMessage' => 'No exam leads found.',
                'indexRoute' => 'leads.certification.index',
            ],
            'study_abroad' => [
                'label' => 'Study Abroad',
                'moduleTitle' => 'Study Abroad Leads',
                'indexPageTitle' => 'Study Abroad Leads',
                'followupsPageTitle' => 'Study Abroad Lead Follow-ups',
                'emptyStateMessage' => 'No study abroad leads found.',
                'indexRoute' => 'leads.study-abroad.index',
            ],
            default => [
                'label' => 'Training',
                'moduleTitle' => 'Training Leads',
                'indexPageTitle' => 'Training Leads',
                'followupsPageTitle' => 'Training Lead Follow-ups',
                'emptyStateMessage' => 'No training leads found.',
                'indexRoute' => 'leads.index',
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    private function leadIndexTabs(string $type): array
    {
        if ($this->supportsAdmission($type)) {
            return [
                'all' => 'All Leads',
                'pending' => 'Pending',
                'registered' => 'Registered',
                'enrolled' => 'Enrolled',
                'not_interesting' => 'Not Interested',
            ];
        }

        return [
            'all' => 'All Leads',
            'pending' => 'Pending',
            'registered' => 'Registered',
            'not_interesting' => 'Not Interested',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function leadIndexBadgeColors(string $type): array
    {
        $colors = [
            'all' => 'badge-secondary',
            'pending' => 'badge-primary',
            'registered' => 'badge-info',
            'not_interesting' => 'badge-danger',
        ];

        if ($this->supportsAdmission($type)) {
            $colors['enrolled'] = 'badge-warning';
        }

        return $colors;
    }

    private function leadInterestHeading(string $type): string
    {
        return match ($this->normalizeLeadType($type)) {
            'coworking' => 'Space Type',
            'certification' => 'Certification / Exam',
            'study_abroad' => 'Study Program',
            default => 'Program',
        };
    }

    private function leadInterestValue(Lead $lead): string
    {
        return match ($this->normalizeLeadType((string) $lead->type)) {
            'coworking' => (string) (data_get($lead->details, 'space_required')
                ?: data_get($lead->details, 'business_name')
                ?: 'N/A'),
            'certification' => (string) (data_get($lead->details, 'certification_title')
                ?: data_get($lead->details, 'exam_code')
                ?: 'N/A'),
            'study_abroad' => (string) (data_get($lead->details, 'preferred_study_program')
                ?: data_get($lead->details, 'preferred_country')
                ?: 'N/A'),
            default => (string) ($lead->program?->title ?? $lead->program?->name ?? 'N/A'),
        };
    }

    /**
     * @param  array<string, mixed>|null  $existingDetails
     * @param  array<string, mixed>  $submittedDetails
     * @return array<string, mixed>
     */
    private function mergeLeadDetails(?array $existingDetails, array $submittedDetails): array
    {
        return array_replace($existingDetails ?? [], $submittedDetails);
    }

    private function renderFollowupsModule(string $type, Request $request): View
    {
        $type = $this->normalizeLeadType($type);
        $search = $this->normalizeSearchTerm((string) $request->query('q', ''));
        $selectedStage = $this->normalizeFollowupStageFilter($request->query('stage'), $type);
        $perPage = $this->resolvePerPage($request);
        $stageConfig = $this->followupStageConfig($type);
        $typeMeta = $this->leadTypeMeta($type);
        $tabs = $stageConfig['tabs'];
        $badgeColors = $stageConfig['badgeColors'];
        $stageMap = $stageConfig['stageMap'];

        $followupQuery = $this->baseFollowupLeadQuery($type);

        if ($search !== null) {
            $this->applyFollowupSearch($followupQuery, $search, $type);
        }

        $totalFollowups = (int) (clone $followupQuery)->count();
        $countsByStage = $this->latestFollowupStageCounts($type, $search);

        if ($selectedStage !== null) {
            $followupQuery->whereHas('latestFollowup', function (Builder $latestFollowupQuery) use ($selectedStage) {
                $latestFollowupQuery->where('stage', $selectedStage);
            });
        }

        $followups = $followupQuery
            ->orderByDesc('latest_followup_at')
            ->orderByDesc('latest_followup_id')
            ->paginate($perPage)
            ->withQueryString();

        $campusDirectory = $type === 'coworking'
            ? Campus::query()->get(['id', 'code', 'city', 'city_abbr', 'name', 'title'])
            : collect();

        $followups->setCollection(
            $followups->getCollection()->map(function (Lead $lead) use ($stageMap, $campusDirectory, $type) {
                $followup = $lead->latestFollowup;

                if (! $followup) {
                    return null;
                }

                $followup->stage = $this->normalizeFollowupStage($type, $followup->stage);
                $followup->stage_label = $stageMap[$followup->stage] ?? ucfirst(str_replace('_', ' ', $followup->stage));
                $followup->followups_count = (int) ($lead->followups_count ?? 0);
                $followup->last_follower_name = trim((string) ($followup->user?->name ?? '')) ?: 'System';
                $followup->setRelation('lead', $lead);

                if ($type === 'coworking') {
                    $followup->branch_code = $this->resolveCoworkingBranchCode($lead, $campusDirectory);
                }

                return $followup;
            })->filter()->values()
        );

        $tabCounts = ['all' => $totalFollowups];
        foreach (array_keys($tabs) as $key) {
            if ($key === 'all') {
                continue;
            }

            $tabCounts[$key] = (int) ($countsByStage[$key] ?? 0);
        }

        $pageTitle = $typeMeta['followupsPageTitle'];
        $moduleTitle = $typeMeta['moduleTitle'];
        $interestHeading = $this->leadInterestHeading($type);

        return view('lead.followups', [
            'followups' => $followups,
            'tabs' => $tabs,
            'badgeColors' => $badgeColors,
            'tabCounts' => $tabCounts,
            'pageTitle' => $pageTitle,
            'moduleTitle' => $moduleTitle,
            'interestHeading' => $interestHeading,
            'type' => $type,
            'selectedStage' => $selectedStage ?? 'all',
            'search' => $search ?? '',
            'perPage' => $perPage,
            'scheduleRoute' => route($this->leadFollowupScheduleRouteName($type)),
        ]);
    }

    private function renderFollowupScheduleModule(string $type, Request $request): View
    {
        $type = $this->normalizeLeadType($type);
        $search = $this->normalizeSearchTerm((string) $request->query('q', ''));
        $selectedWindow = $this->normalizeFollowupScheduleWindow($request->query('window')) ?? 'today';
        $perPage = $this->resolvePerPage($request);
        $typeMeta = $this->leadTypeMeta($type);
        $scheduleConfig = $this->followupScheduleConfig();
        $tabs = $scheduleConfig['tabs'];
        $badgeColors = $scheduleConfig['badgeColors'];
        $stageMap = $this->followupStageConfig($type)['stageMap'];

        $followupQuery = $this->baseFollowupLeadQuery($type);

        if ($search !== null) {
            $this->applyFollowupSearch($followupQuery, $search, $type);
        }

        $this->applyFollowupScheduleWindowFilter($followupQuery, $selectedWindow);

        $countsByWindow = $this->latestFollowupScheduleCounts($type, $search);

        $followups = $followupQuery
            ->orderBy('latest_next_action_date')
            ->orderByDesc('latest_followup_id')
            ->paginate($perPage)
            ->withQueryString();

        $campusDirectory = $type === 'coworking'
            ? Campus::query()->get(['id', 'code', 'city', 'city_abbr', 'name', 'title'])
            : collect();

        $followups->setCollection(
            $followups->getCollection()->map(function (Lead $lead) use ($campusDirectory, $stageMap, $type) {
                $followup = $lead->latestFollowup;

                if (! $followup || ! $followup->next_action_date) {
                    return null;
                }

                $followup->stage = $this->normalizeFollowupStage($type, $followup->stage);
                $followup->stage_label = $stageMap[$followup->stage] ?? ucfirst(str_replace('_', ' ', $followup->stage));
                $followup->followups_count = (int) ($lead->followups_count ?? 0);
                $followup->last_follower_name = trim((string) ($followup->user?->name ?? '')) ?: 'System';
                $lead->interest_summary = $this->leadInterestValue($lead);
                $followup->setRelation('lead', $lead);

                if ($type === 'coworking') {
                    $followup->branch_code = $this->resolveCoworkingBranchCode($lead, $campusDirectory);
                }

                return $followup;
            })->filter()->values()
        );

        return view('lead.followup_schedule', [
            'followups' => $followups,
            'tabs' => $tabs,
            'badgeColors' => $badgeColors,
            'tabCounts' => $countsByWindow,
            'pageTitle' => ($typeMeta['moduleTitle'] . ' Follow-up Schedule'),
            'moduleTitle' => $typeMeta['moduleTitle'],
            'interestHeading' => $this->leadInterestHeading($type),
            'type' => $type,
            'selectedWindow' => $selectedWindow,
            'search' => $search ?? '',
            'perPage' => $perPage,
            'stageRoute' => route($this->leadFollowupsRouteName($type)),
        ]);
    }

    private function normalizeSearchTerm(?string $value): ?string
    {
        $search = trim((string) $value);

        return $search !== '' ? $search : null;
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 25);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }

    private function normalizeLeadStatusFilter(mixed $value, string $type): ?string
    {
        $status = trim((string) $value);

        return $status !== ''
            && $status !== 'all'
            && array_key_exists($status, $this->leadIndexTabs($type))
            ? $status
            : null;
    }

    private function normalizeFollowupStageFilter(mixed $value, string $type): ?string
    {
        $stage = trim((string) $value);

        return $stage !== ''
            && $stage !== 'all'
            && array_key_exists($stage, $this->followupStageConfig($type)['stageMap'])
            ? $stage
            : null;
    }

    private function normalizeFollowupScheduleWindow(mixed $value): ?string
    {
        $window = trim((string) $value);

        return in_array($window, ['pending', 'today', 'upcoming'], true)
            ? $window
            : null;
    }

    private function applyLeadIndexSearch(Builder $query, string $search, string $type): Builder
    {
        return $this->applyLeadSearch($query, $search, $type, false);
    }

    private function applyFollowupSearch(Builder $query, string $search, string $type): Builder
    {
        return $this->applyLeadSearch($query, $search, $type, true);
    }

    private function applyLeadSearch(Builder $query, string $search, string $type, bool $includeLatestFollower): Builder
    {
        $like = $this->toSqlLikePattern($search);

        return $query->where(function (Builder $searchQuery) use ($like, $type, $includeLatestFollower) {
            $searchQuery
                ->where('name', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('city', 'like', $like)
                ->orWhere('origin', 'like', $like)
                ->orWhere('marketing_source', 'like', $like)
                ->orWhereHas('campus', function (Builder $campusQuery) use ($like) {
                    $campusQuery
                        ->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('title', 'like', $like)
                        ->orWhere('city', 'like', $like);
                })
                ->orWhereHas('createdBy', fn (Builder $userQuery) => $userQuery->where('name', 'like', $like));

            if ($type === 'training') {
                $searchQuery->orWhereHas('program', function (Builder $programQuery) use ($like) {
                    $programQuery
                        ->where('title', 'like', $like)
                        ->orWhere('name', 'like', $like);
                });
            } elseif ($type === 'coworking') {
                $searchQuery
                    ->orWhere('details->space_required', 'like', $like)
                    ->orWhere('details->business_name', 'like', $like)
                    ->orWhere('details->preferred_location', 'like', $like);
            } elseif ($type === 'certification') {
                $searchQuery
                    ->orWhere('details->certification_title', 'like', $like)
                    ->orWhere('details->exam_code', 'like', $like);
            } elseif ($type === 'study_abroad') {
                $searchQuery
                    ->orWhere('details->preferred_study_program', 'like', $like)
                    ->orWhere('details->preferred_country', 'like', $like);
            }

            if ($includeLatestFollower) {
                $searchQuery
                    ->orWhereHas('latestFollowup.user', fn (Builder $userQuery) => $userQuery->where('name', 'like', $like))
                    ->orWhereHas('latestFollowup', fn (Builder $followupQuery) => $followupQuery->where('stage', 'like', $like));
            }
        });
    }

    private function toSqlLikePattern(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);

        return '%' . $escaped . '%';
    }

    /**
     * @return array{
     *     tabs: array<string, string>,
     *     badgeColors: array<string, string>
     * }
     */
    private function followupScheduleConfig(): array
    {
        return [
            'tabs' => [
                'today' => 'Todays Follow Up',
                'pending' => 'Pending Follow Up',
                'upcoming' => 'Upcoming Follow Up',
            ],
            'badgeColors' => [
                'today' => 'badge-success',
                'pending' => 'badge-danger',
                'upcoming' => 'badge-danger',
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function latestFollowupScheduleCounts(string $type, ?string $search = null): array
    {
        $baseQuery = $this->baseFollowupLeadQuery($type);

        if ($search !== null) {
            $this->applyFollowupSearch($baseQuery, $search, $type);
        }

        $counts = [];

        foreach (array_keys($this->followupScheduleConfig()['tabs']) as $window) {
            $counts[$window] = (int) $this->applyFollowupScheduleWindowFilter(
                clone $baseQuery,
                $window
            )->count();
        }

        return $counts;
    }

    private function applyFollowupScheduleWindowFilter(Builder $query, ?string $window): Builder
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        if ($window === 'pending') {
            $query->where('status', 'pending');
        }

        return $query->whereHas('latestFollowup', function (Builder $latestFollowupQuery) use ($todayEnd, $todayStart, $window) {
            $latestFollowupQuery->whereNotNull('next_action_date');

            switch ($window) {
                case 'pending':
                    $latestFollowupQuery->where('next_action_date', '<', $todayStart);
                    break;

                case 'today':
                    $latestFollowupQuery->whereBetween('next_action_date', [$todayStart, $todayEnd]);
                    break;

                case 'upcoming':
                    $latestFollowupQuery->where('next_action_date', '>', $todayEnd);
                    break;
            }
        });
    }

    private function followupStageConfig(string $type): array
    {
        if ($type === 'training') {
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
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'need_analysis' => 'Need Analysis',
                    'branch_visited' => 'Branch Visited',
                    'proposal_negotiation' => 'Proposal & Negotiation',
                    'not_interesting' => 'Not Interesting',
                    'registered' => 'Registered',
                    'enroll' => 'Enrolled',
                ],
                'badgeColors' => [
                    'all' => 'badge-secondary',
                    'new' => 'badge-primary',
                    'contacted' => 'badge-success',
                    'need_analysis' => 'badge-warning',
                    'branch_visited' => 'badge-secondary',
                    'proposal_negotiation' => 'badge-info',
                    'not_interesting' => 'badge-warning',
                    'registered' => 'badge-success',
                    'enroll' => 'badge-success',
                ],
            ];
        }

        if (in_array($type, ['coworking', 'certification', 'study_abroad'], true)) {
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
                    'new' => 'New',
                    'contacted' => 'Contacted',
                    'need_analysis' => 'Need Analysis',
                    'branch_visited' => 'Branch Visited',
                    'proposal_negotiation' => 'Proposal & Negotiation',
                    'registered' => 'Registered',
                    'not_interesting' => 'Not Interesting',
                ],
                'badgeColors' => [
                    'all' => 'badge-secondary',
                    'new' => 'badge-primary',
                    'contacted' => 'badge-success',
                    'need_analysis' => 'badge-warning',
                    'branch_visited' => 'badge-secondary',
                    'proposal_negotiation' => 'badge-info',
                    'registered' => 'badge-success',
                    'not_interesting' => 'badge-warning',
                ],
            ];
        }

        return $this->followupStageConfig('training');
    }

    private function leadFollowupsRouteName(?string $type): string
    {
        return match ($type) {
            'certification' => 'leads.certification.followups',
            'study_abroad' => 'leads.study-abroad.followups',
            'coworking' => 'leads.coworking.followups',
            default => 'leads.followups',
        };
    }

    private function leadFollowupScheduleRouteName(?string $type): string
    {
        return match ($type) {
            'certification' => 'leads.certification.followup-schedule',
            'study_abroad' => 'leads.study-abroad.followup-schedule',
            'coworking' => 'leads.coworking.followup-schedule',
            default => 'leads.followup-schedule',
        };
    }

    private function usesTrainingConversionFlow(?string $type): bool
    {
        return $type === 'training';
    }

    private function supportsRegistration(?string $type): bool
    {
        return in_array($type, ['training', 'coworking'], true);
    }

    private function supportsAdmission(?string $type): bool
    {
        return $type === 'training';
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

        if (! $this->supportsAdmission($type) && $normalizedStage === 'enroll') {
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

    private function resolveInitialFollowupMethod(?string $origin): ?string
    {
        $method = trim((string) $origin);

        return $method !== '' ? $method : null;
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

    private function ensureLeadCreatePermission(Request $request): void
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'You do not have permission to create leads.');
        }

        if ($user->hasAnyPermission(['lead.create'])) {
            return;
        }

        $isWebLeadConversion = $request->filled('web_lead') || $request->filled('web_lead_id');

        abort_unless(
            $isWebLeadConversion && $user->hasAnyPermission(['web-lead.view', 'web-lead.create']),
            403,
            'You do not have permission to create leads.'
        );
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
                'campus_id' => ['required', 'integer', 'exists:campuses,id'],
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
                'campus_id' => ['required', 'integer', 'exists:campuses,id'],
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
