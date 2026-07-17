<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\FinancePayee;
use App\Models\FinancePayeeBankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PayeeController extends Controller
{
    public function index(Request $request): View
    {
        return view('finance.payee.index', [
            'payees' => FinancePayee::query()->with(['campus', 'bankAccounts'])->orderByDesc('id')->paginate(20),
            'campuses' => Campus::query()->orderBy('name')->get(['id', 'name', 'code']),
            'canCreatePayees' => $this->canCreatePayees($request),
            'canViewPayees' => $this->canViewPayees($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['supplier', 'payee', 'employee'])],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'full_name' => ['required', 'string', 'max:200'],
            'display_name' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:200'],
            'cnic' => ['nullable', 'string', 'max:50'],
            'postal_address' => ['nullable', 'string', 'max:500'],
            'company_name' => ['nullable', 'string', 'max:200'],
            'company_website' => ['nullable', 'string', 'max:200'],
            'tax_registration_no' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
            'employee_code' => ['nullable', 'string', 'max:100'],
            'designation' => ['nullable', 'string', 'max:150'],
            'monthly_salary' => ['nullable', 'numeric', 'min:0'],
            'joining_date' => ['nullable', 'date'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'account_title' => ['nullable', 'string', 'max:150'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'iban' => ['nullable', 'string', 'max:100'],
        ]);

        $payee = FinancePayee::create([
            'campus_id' => $validated['campus_id'] ?? null,
            'type' => $validated['type'],
            'employee_code' => $validated['employee_code'] ?? null,
            'designation' => $validated['designation'] ?? null,
            'monthly_salary' => $validated['monthly_salary'] ?? null,
            'joining_date' => $validated['joining_date'] ?? null,
            'full_name' => $validated['full_name'],
            'display_name' => $validated['display_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'mobile' => $validated['mobile'] ?? null,
            'email' => $validated['email'] ?? null,
            'cnic' => $validated['cnic'] ?? null,
            'postal_address' => $validated['postal_address'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_website' => $validated['company_website'] ?? null,
            'tax_registration_no' => $validated['tax_registration_no'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? null,
            'status' => 'active',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        if (!empty($validated['bank_name']) || !empty($validated['account_number']) || !empty($validated['iban'])) {
            FinancePayeeBankAccount::create([
                'payee_id' => $payee->id,
                'bank_name' => $validated['bank_name'] ?? null,
                'account_title' => $validated['account_title'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'iban' => $validated['iban'] ?? null,
                'is_primary' => true,
            ]);
        }

        return back()->with('status', 'Supplier/Payee saved successfully.');
    }

    private function canCreatePayees(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.payee.create']) ?? false;
    }

    private function canViewPayees(Request $request): bool
    {
        return $request->user()?->hasAnyPermission(['finance.payee.view']) ?? false;
    }
}
