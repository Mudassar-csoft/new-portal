<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AccessMap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserSetupController extends Controller
{
    public function show(Request $request, User $user): View
    {
        return view('auth.user-setup', [
            'user' => $user,
            'signedUrl' => $request->fullUrl(),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(AccessMap::firstAccessibleRouteFor($user))
            ->with('status', 'Welcome! Your password has been set.');
    }
}
