<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna platform (Admin, Guru, Siswa).
     */
    public function index(Request $request): Response
    {
        $role = $request->query('role');
        $search = $request->query('search');
        $schoolId = $request->query('school_id');

        $query = User::query()->with('school:id,nama,jenjang');

        if ($role && in_array($role, ['admin', 'guru', 'siswa'])) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();
        $schools = School::orderBy('nama')->get(['id', 'nama', 'jenjang']);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'schools' => $schools,
            'filters' => [
                'role' => $role,
                'search' => $search,
                'school_id' => $schoolId,
            ],
            'currentAdminId' => Auth::id(),
        ]);
    }

    /**
     * Daftarkan pengguna baru (Admin atau Guru).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,guru'],
            'password' => ['required', 'string', 'min:6'],
            'school_id' => ['nullable', 'exists:schools,id'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'school_id' => $validated['school_id'] ?? null,
        ]);

        return back()->with('success', "Pengguna {$validated['name']} ({$validated['role']}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'school_id' => ['nullable', 'exists:schools,id'],
        ]);

        $user->update($validated);

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Reset kata sandi untuk Guru atau Admin.
     */
    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6'],
        ], [
            'new_password.min' => 'Kata sandi baru minimal 6 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return back()->with('success', "Kata sandi untuk {$user->name} berhasil diatur ulang.");
    }

    /**
     * Reset PIN 4-digit untuk Siswa.
     */
    public function resetPin(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->role !== 'siswa') {
            return back()->withErrors(['pin' => 'Hanya akun siswa yang memiliki PIN 4-digit.']);
        }

        $validated = $request->validate([
            'new_pin' => ['required', 'regex:/^[0-9]{4}$/'],
        ], [
            'new_pin.required' => 'PIN baru wajib diisi.',
            'new_pin.regex' => 'PIN harus terdiri dari 4 digit angka.',
        ]);

        $user->update([
            'pin_hash' => Hash::make($validated['new_pin']),
        ]);

        return back()->with('success', "PIN siswa {$user->name} berhasil diatur ulang menjadi {$validated['new_pin']}.");
    }

    /**
     * Hapus pengguna dari sistem.
     */
    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.']);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Pengguna {$name} berhasil dihapus.");
    }
}
