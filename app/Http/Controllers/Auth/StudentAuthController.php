<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StudentLoginRequest;
use App\Services\Auth\StudentAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StudentAuthController extends Controller
{
    public function __construct(
        protected StudentAuthService $authService
    ) {}

    /**
     * Tampilkan halaman masuk siswa.
     */
    public function showLoginForm(Request $request): Response|RedirectResponse
    {
        if (Auth::check() && Auth::user()->role === 'siswa') {
            return redirect()->route('student.dashboard');
        }

        return Inertia::render('Auth/StudentLogin', [
            'initialCode' => $request->query('code', ''),
        ]);
    }

    /**
     * Proses autentikasi siswa dengan rate limiting ketat.
     */
    public function login(StudentLoginRequest $request): RedirectResponse
    {
        $throttleKey = 'student-login:' . Str::transliterate(Str::lower($request->input('kode_kelas')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'pin' => ["Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik."],
            ]);
        }

        try {
            $this->authService->loginOrCreateStudent(
                $request->input('kode_kelas'),
                $request->input('nama_panggilan'),
                $request->input('pin')
            );

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('student.dashboard'))
                ->with('success', 'Selamat datang! Kamu berhasil masuk.');
        } catch (ValidationException $e) {
            RateLimiter::hit($throttleKey, 60);
            throw $e;
        }
    }

    /**
     * Keluar dari sesi siswa.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login')
            ->with('message', 'Kamu telah keluar dari akun.');
    }
}
