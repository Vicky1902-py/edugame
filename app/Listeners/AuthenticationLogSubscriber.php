<?php

namespace App\Listeners;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;

class AuthenticationLogSubscriber
{
    /**
     * Handle successful login.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        $ip = request()->ip();
        $userAgent = request()->userAgent();

        LoginLog::create([
            'user_id' => $user?->getAuthIdentifier(),
            'email_attempted' => $user?->email ?? ($user?->username ?? 'auth-user'),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status' => 'success',
            'failure_reason' => null,
            'created_at' => now(),
        ]);

        if ($user instanceof User) {
            $user->updateQuietly([
                'last_login_at' => now(),
                'last_login_ip' => $ip,
            ]);
        }
    }

    /**
     * Handle failed login attempt.
     */
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? ($event->user?->email ?? request()->input('email', 'unknown'));
        $ip = request()->ip();
        $userAgent = request()->userAgent();

        LoginLog::create([
            'user_id' => $event->user?->getAuthIdentifier(),
            'email_attempted' => is_string($email) ? $email : 'unknown',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status' => 'failed',
            'failure_reason' => 'Kredensial tidak valid',
            'created_at' => now(),
        ]);
    }

    /**
     * Handle lockout event.
     */
    public function handleLockout(Lockout $event): void
    {
        $email = request()->input('email', 'unknown');
        $ip = request()->ip();
        $userAgent = request()->userAgent();

        LoginLog::create([
            'user_id' => null,
            'email_attempted' => is_string($email) ? $email : 'unknown',
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status' => 'locked',
            'failure_reason' => 'Terlalu banyak percobaan login gagal (rate limit lockout)',
            'created_at' => now(),
        ]);
    }
}
