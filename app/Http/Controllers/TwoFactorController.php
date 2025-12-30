<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA verification form
     */
    public function show()
    {
        $user = auth()->user();

        if (!$user->two_factor_enabled) {
            return redirect()->route('dashboard')
                ->with('error', '2FA tidak diaktifkan untuk akun Anda.');
        }

        return view('auth.two-factor-challenge', [
            'email' => $user->email,
            'expiresAt' => $user->two_factor_expires_at,
        ]);
    }

    /**
     * Verify 2FA code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($user->verifyTwoFactorCode($request->code)) {
            $user->markTwoFactorVerified();

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'property_id' => $user->property_id,
                'action' => 'two_factor_verified',
                'description' => "{$user->name} berhasil verifikasi 2FA",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('dashboard'))
                ->with('success', '✅ Verifikasi 2FA berhasil!');
        }

        return back()
            ->withErrors(['code' => 'Kode verifikasi tidak valid atau sudah kadaluarsa.'])
            ->withInput();
    }

    /**
     * Resend 2FA code
     */
    public function resend(Request $request)
    {
        $user = auth()->user();

        if (!$user->two_factor_enabled) {
            return back()->with('error', '2FA tidak diaktifkan.');
        }

        // Generate new code
        $code = $user->generateTwoFactorCode();

        // Send code via email
        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));

            return back()->with('success', 'Kode verifikasi baru telah dikirim ke email Anda.');
        } catch (\Exception $e) {
            Log::error('Failed to resend 2FA code', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal mengirim kode verifikasi. Silakan coba lagi.');
        }
    }

    /**
     * Enable 2FA for user account
     */
    public function enable(Request $request)
    {
        $user = auth()->user();

        if ($user->two_factor_enabled) {
            return back()->with('info', '2FA sudah diaktifkan untuk akun Anda.');
        }

        $user->update([
            'two_factor_enabled' => true,
        ]);

        // Log activity
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $user->property_id,
            'action' => 'two_factor_enabled',
            'description' => "{$user->name} mengaktifkan 2FA",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', '✅ Two-Factor Authentication telah diaktifkan untuk akun Anda.');
    }

    /**
     * Disable 2FA for user account
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();

        if (!$user->two_factor_enabled) {
            return back()->with('info', '2FA sudah dinonaktifkan.');
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
            'two_factor_verified_at' => null,
        ]);

        // Log activity
        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'property_id' => $user->property_id,
            'action' => 'two_factor_disabled',
            'description' => "{$user->name} menonaktifkan 2FA",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', '2FA telah dinonaktifkan untuk akun Anda.');
    }
}
