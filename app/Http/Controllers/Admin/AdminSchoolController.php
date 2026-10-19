<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminSchoolController extends Controller
{
    /**
     * Tampilkan daftar sekolah terdaftar di platform.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $jenjang = $request->query('jenjang');
        $status = $request->query('status');

        $query = School::query()
            ->withCount([
                'classes',
                'users as teachers_count' => fn ($q) => $q->where('role', 'guru'),
                'users as students_count' => fn ($q) => $q->where('role', 'siswa'),
            ]);

        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        if ($jenjang && in_array($jenjang, ['SD', 'SMP', 'SMA', 'SMK'])) {
            $query->where('jenjang', $jenjang);
        }

        if ($status && in_array($status, ['aktif', 'nonaktif', 'trial'])) {
            $query->where('status_lisensi', $status);
        }

        $schools = $query->latest('id')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Schools/Index', [
            'schools' => $schools,
            'filters' => [
                'search' => $search,
                'jenjang' => $jenjang,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Daftarkan sekolah baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'in:SD,SMP,SMA,SMK'],
            'status_lisensi' => ['required', 'in:aktif,nonaktif,trial'],
        ], [
            'nama.required' => 'Nama sekolah wajib diisi.',
            'jenjang.required' => 'Pilih jenjang pendidikan.',
            'status_lisensi.required' => 'Pilih status lisensi.',
        ]);

        School::create($validated);

        return back()->with('success', 'Sekolah baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data sekolah.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $school = School::findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jenjang' => ['required', 'in:SD,SMP,SMA,SMK'],
            'status_lisensi' => ['required', 'in:aktif,nonaktif,trial'],
        ]);

        $school->update($validated);

        return back()->with('success', 'Data sekolah berhasil diperbarui.');
    }

    /**
     * Hapus sekolah dari platform.
     */
    public function destroy(int $id): RedirectResponse
    {
        $school = School::findOrFail($id);

        // Periksa keterikatan data
        if ($school->users()->count() > 0) {
            return back()->withErrors([
                'delete' => 'Sekolah ini tidak dapat dihapus karena masih memiliki guru atau siswa aktif.',
            ]);
        }

        $school->delete();

        return back()->with('success', 'Sekolah berhasil dihapus.');
    }
}
