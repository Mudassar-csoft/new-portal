<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function requestOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = now()->addMinutes(15);

            DB::table('password_reset_otps')
                ->where('user_id', $user->id)
                ->whereNull('used_at')
                ->update(['used_at' => now(), 'updated_at' => now()]);

            DB::table('password_reset_otps')->insert([
                'user_id' => $user->id,
                'email' => $user->email,
                'otp_hash' => Hash::make($otp),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            try {
                Mail::send(new PasswordResetOtpMail(
                    user: $user,
                    otp: $otp,
                    expiresAt: $expiresAt->format('d M Y, h:i A'),
                    requestIp: $request->ip() ?? 'unknown',
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()
            ->route('password.reset.form', ['email' => $validated['email']])
            ->with('status', 'If an account exists for that email, an OTP has been sent to the system administrator. Please contact them to receive it.');
    }

    public function showResetForm(Request $request): View
    {
        return view('auth.reset-password', [
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        $otpRow = DB::table('password_reset_otps')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$otpRow || !Hash::check($validated['otp'], $otpRow->otp_hash)) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_otps')
            ->where('id', $otpRow->id)
            ->update(['used_at' => now(), 'updated_at' => now()]);

        DB::table('password_reset_otps')
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now(), 'updated_at' => now()]);

        return redirect()
            ->route('login')
            ->with('status', 'Password updated successfully. Please log in.');
    }
}
