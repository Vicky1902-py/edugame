<?php

use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Teacher\ClassroomController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Models\Question;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Halaman Publik
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'stats' => [
            'schools' => School::count(),
            'teachers' => User::where('role', 'guru')->count(),
            'students' => User::where('role', 'siswa')->count(),
            'questions' => Question::count(),
        ],
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Autentikasi Siswa (Tanpa Email, Kode Kelas + Nama + PIN)
|--------------------------------------------------------------------------
*/
Route::get('/masuk-siswa', [StudentAuthController::class, 'showLoginForm'])->name('student.login');
Route::post('/masuk-siswa', [StudentAuthController::class, 'login'])->name('student.login.submit');

/*
|--------------------------------------------------------------------------
| Autentikasi Guru (Email + Password)
|--------------------------------------------------------------------------
*/
Route::get('/login-guru', [TeacherAuthController::class, 'showLoginForm'])->name('teacher.login');
Route::post('/login-guru', [TeacherAuthController::class, 'login'])->name('teacher.login.submit');
Route::get('/daftar-guru', [TeacherAuthController::class, 'showRegisterForm'])->name('teacher.register');
Route::post('/daftar-guru', [TeacherAuthController::class, 'register'])->name('teacher.register.submit');

/*
|--------------------------------------------------------------------------
| Area Siswa (Role: Siswa)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:siswa'])->group(function () {
    Route::get('/siswa/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    Route::post('/siswa/logout', [StudentAuthController::class, 'logout'])->name('student.logout');
});

/*
|--------------------------------------------------------------------------
| Area Guru (Role: Guru, Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:guru,admin'])->group(function () {
    Route::get('/guru/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    
    // Manajemen Kelas
    Route::get('/guru/kelas', [ClassroomController::class, 'index'])->name('teacher.classes.index');
    Route::post('/guru/kelas', [ClassroomController::class, 'store'])->name('teacher.classes.store');
    Route::get('/guru/kelas/{id}', [ClassroomController::class, 'show'])->name('teacher.classes.show');
    Route::put('/guru/kelas/{id}', [ClassroomController::class, 'update'])->name('teacher.classes.update');
    Route::delete('/guru/kelas/{id}', [ClassroomController::class, 'destroy'])->name('teacher.classes.destroy');

    Route::post('/guru/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');
});
