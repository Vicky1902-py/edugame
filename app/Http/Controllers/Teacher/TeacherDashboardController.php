<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TeacherDashboardController extends Controller
{
    /**
     * Tampilkan ringkasan beranda dashboard guru.
     */
    public function index(Request $request): Response
    {
        $teacher = Auth::user();

        $classes = SchoolClass::where('guru_id', $teacher->id)
            ->withCount('enrollments')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalStudents = Enrollment::whereIn('class_id', $classes->pluck('id'))->distinct('student_id')->count('student_id');
        $totalQuestions = Question::count();

        return Inertia::render('Teacher/Dashboard', [
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
            ],
            'stats' => [
                'total_classes' => $classes->count(),
                'total_students' => $totalStudents,
                'total_questions' => $totalQuestions,
            ],
            'recentClasses' => $classes->take(4),
        ]);
    }
}
