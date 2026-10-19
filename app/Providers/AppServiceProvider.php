<?php

namespace App\Providers;

use App\Listeners\AuthenticationLogSubscriber;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Superadmin lolos semua pemeriksaan izin via Gate::before
        Gate::before(function ($user, string $ability) {
            return ($user instanceof User && $user->isSuperadmin()) ? true : null;
        });

        // Pencatatan aktivitas otentikasi ke tabel login_logs
        Event::listen(Login::class, [AuthenticationLogSubscriber::class, 'handleLogin']);
        Event::listen(Failed::class, [AuthenticationLogSubscriber::class, 'handleFailed']);
        Event::listen(Lockout::class, [AuthenticationLogSubscriber::class, 'handleLockout']);
    }
}
