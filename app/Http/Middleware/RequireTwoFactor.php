<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip if on 2FA-related routes
        if ($request->is('two-factor/*') || $request->is('logout')) {
            return $next($request);
        }

        // Check if user needs 2FA verification
        if ($user->needsTwoFactorVerification()) {
            // Generate and send 2FA code if not already sent
            if (!$user->two_factor_code || !$user->two_factor_expires_at || $user->two_factor_expires_at->isPast()) {
                $code = $user->generateTwoFactorCode();

                // Send code via email
                try {
                    \Mail::to($user->email)->send(new \App\Mail\TwoFactorCodeMail($user, $code));
                } catch (\Exception $e) {
                    \Log::error('Failed to send 2FA code email', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return redirect()->route('two-factor.show')
                ->with('info', 'Untuk keamanan akun Anda, silakan verifikasi kode 2FA yang dikirim ke email Anda.');
        }

        return $next($request);
    }
}
