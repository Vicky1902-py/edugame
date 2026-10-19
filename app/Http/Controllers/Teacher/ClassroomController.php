<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\StoreClassRequest;
use App\Http\Requests\Classroom\UpdateClassRequest;
use App\Services\Classroom\ClassroomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomController extends Controller
{
    public function __construct(
        protected ClassroomService $classroomService
    ) {}

    /**
     * Tampilkan daftar kelas yang dibuat guru.
     */
    public function index(): Response
    {
        $teacher = Auth::user();
        $classes = $this->classroomService->getTeacherClasses($teacher->id);

        return Inertia::render('Teacher/Classrooms/Index', [
            'classes' => $classes,
            'teacher' => [
                'name' => $teacher->name,
                'email' => $teacher->email,
            ],
        ]);
    }

    /**
     * Simpan kelas baru.
     */
    public function store(StoreClassRequest $request): RedirectResponse
    {
        $class = $this->classroomService->createClass(
            $request->user(),
            $request->validated()
        );

        return redirect()->route('teacher.classes.show', $class->id)
            ->with('success', "Kelas '{$class->nama}' berhasil dibuat dengan kode gabung: {$class->kode_gabung}");
    }

    /**
     * Tampilkan detail kelas dan daftar siswa.
     */
    public function show(int $id): Response
    {
        $class = $this->classroomService->getClassDetails($id, Auth::id());

        return Inertia::render('Teacher/Classrooms/Show', [
            'classroom' => $class,
        ]);
    }

    /**
     * Perbarui pengaturan kelas.
     */
    public function update(int $id, UpdateClassRequest $request): RedirectResponse
    {
        $class = $this->classroomService->updateClass($id, Auth::id(), $request->validated());

        return redirect()->back()
            ->with('success', "Pengaturan kelas '{$class->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus kelas.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->classroomService->deleteClass($id, Auth::id());

        return redirect()->route('teacher.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
