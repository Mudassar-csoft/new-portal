<?php

namespace App\Http\Controllers\Hrm;

use App\Models\HrDocument;
use App\Models\HrEmployee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentController extends BaseController
{
    public function index(Request $request): View
    {
        $this->authorizeHrm($request, ['hrm_document.view']);

        $documents = HrDocument::query()
            ->with(['employee:id,employee_code,first_name,last_name', 'uploader:id,name'])
            ->when($request->integer('employee_id'), fn ($q, $employeeId) => $q->where('employee_id', $employeeId))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('hrm.documents.index', [
            'documents' => $documents,
            'employees' => HrEmployee::query()->where('status', 'active')->orderBy('first_name')->limit(400)->get(['id', 'employee_code', 'first_name', 'last_name']),
            'filters' => [
                'employee_id' => $request->integer('employee_id') ?: null,
                'status' => $request->input('status'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeHrm($request, ['hrm_document.upload']);

        $validated = $request->validate([
            'employee_id' => ['required', 'exists:hr_employees,id'],
            'document_type' => ['required', Rule::in([
                'offer_letter',
                'cnic_copy',
                'degree',
                'contract',
                'nda',
                'experience_letter',
                'warning_letter',
                'other',
            ])],
            'title' => ['required', 'string', 'max:180'],
            'file' => ['required', 'file', 'max:10240'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'reminder_days_before' => ['nullable', 'integer', 'min:0', 'max:365'],
            'status' => ['nullable', Rule::in(['active', 'expired'])],
            'notes' => ['nullable', 'string'],
        ]);

        $filePath = $request->file('file')?->store('hrm/documents', 'public');
        if (!$filePath) {
            return back()->withErrors(['file' => 'Document upload failed.'])->withInput();
        }

        HrDocument::query()->create([
            'employee_id' => $validated['employee_id'],
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'file_path' => $filePath,
            'issue_date' => $validated['issue_date'] ?? null,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'reminder_days_before' => (int) ($validated['reminder_days_before'] ?? 30),
            'status' => $validated['status'] ?? 'active',
            'notes' => $validated['notes'] ?? null,
            'uploaded_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Document uploaded.');
    }
}

