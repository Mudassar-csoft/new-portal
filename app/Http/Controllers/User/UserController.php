<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\User;
use App\Models\User\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = User::with(['campus', 'roles'])->select('users.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', fn (User $user) => e($user->name))
                ->editColumn('email', fn (User $user) => e($user->email))
                ->addColumn('role', function (User $user) {
                    $roles = $user->roles->pluck('name');

                    if ($roles->isEmpty()) {
                        return '<span class="text-muted">N/A</span>';
                    }

                    return e($roles->first());
                })
                ->addColumn('status', fn () => '<span class="label label-success">Active</span>')
                ->addColumn('campus_code', fn (User $user) => e(data_get($user, 'campus.code', 'N/A')))
                ->addColumn('date', fn (User $user) => optional($user->created_at)->format('d-M-Y') ?? 'N/A')
                ->addColumn('actions', fn (User $user) => view('user.partials.action', ['user' => $user])->render())
                ->filterColumn('role', function ($query, $keyword) {
                    $query->whereHas('roles', function ($roleQuery) use ($keyword) {
                        $roleQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('campus_code', function ($query, $keyword) {
                    $query->whereHas('campus', function ($campusQuery) use ($keyword) {
                        $campusQuery->where('code', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('user.index');
    }

    public function create(): View
    {
        $campuses = Campus::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('user.create', compact('campuses', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        try {
            $user = User::create([
                'campus_id' => $validated['campus_id'] ?? null,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            if (!empty($validated['roles'])) {
                $user->roles()->sync(
                    collect($validated['roles'])->mapWithKeys(fn ($id) => [
                        $id => ['assigned_by' => optional($request->user())->id],
                    ])
                );
            }

            return redirect()->route('users.index')->with('status', 'User created.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to save the user right now. Please try again.');
        }
    }

    public function edit(User $user): View
    {
        $campuses = Campus::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        $user->load('roles');

        return view('user.edit', compact('user', 'campuses', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->fill([
            'campus_id' => $validated['campus_id'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->roles()->sync(
            collect($validated['roles'] ?? [])->mapWithKeys(fn ($id) => [
                $id => ['assigned_by' => optional($request->user())->id],
            ])
        );

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->update(['at_deleted' => now()]);

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }
}







