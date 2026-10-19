<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TeacherLoginRequest;
use App\Http\Requests\Auth\TeacherRegisterRequest;
use App\Services\Auth\TeacherAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAuthController extends Controller
{
    public function __construct(
        protected TeacherAuthService $authService
    ) {}

    /**
     * Tampilkan formulir login guru.
     */
    public function showLoginForm(): Response|RedirectResponse
    {
        if (Auth::check() && in_array(Auth::user()->role, ['guru', 'admin'])) {
            return redirect()->route('teacher.dashboard');
        }

        return Inertia::render('Auth/TeacherLogin');
    }

    /**
     * Proses login guru.
     */
    public function login(TeacherLoginRequest $request): RedirectResponse
    {
        $throttleKey = 'teacher-login:' . Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ["Terlalu banyak percobaan login. Silakan tunggu {$seconds} detik."],
            ]);
        }

        try {
            $this->authService->loginTeacher(
                $request->input('email'),
                $request->input('password')
            );

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('teacher.dashboard'))
                ->with('success', 'Selamat datang kembali, Bapak/Ibu Guru!');
        } catch (ValidationException $e) {
            RateLimiter::hit($throttleKey, 60);
            throw $e;
        }
    }

    /**
     * Tampilkan formulir pendaftaran guru baru.
     */
    public function showRegisterForm(): Response|RedirectResponse
    {
        if (Auth::check() && in_array(Auth::user()->role, ['guru', 'admin'])) {
            return redirect()->route('teacher.dashboard');
        }

        return Inertia::render('Auth/TeacherRegister');
    }

    /**
     * Proses pendaftaran akun guru baru.
     */
    public function register(TeacherRegisterRequest $request): RedirectResponse
    {
        $teacher = $this->authService->registerTeacher($request->validated());
        $request->session()->regenerate();

        return redirect()->route('teacher.dashboard')
            ->with('success', 'Pendaftaran berhasil! Selamat datang di EduGame Indonesia.');
    }

    /**
     * Keluar dari sesi guru.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('teacher.login')
            ->with('message', 'Anda telah berhasil keluar.');
    }
}
