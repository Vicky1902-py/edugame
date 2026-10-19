<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if (in_array('guru', $roles)) {
                return redirect()->route('teacher.login');
            }
            return redirect()->route('student.login');
        }

        if (!in_array($user->role, $roles)) {
            if ($user->role === 'guru') {
                return redirect()->route('teacher.dashboard');
            }
            if ($user->role === 'siswa') {
                return redirect()->route('student.dashboard');
            }

            abort(403, 'Akses tidak diizinkan.');
        }

        return $next($request);
    }
}
