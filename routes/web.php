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
Route::get('/login', fn () => redirect()->route('student.login'))->name('login');
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

    // Game Balapan Perkalian & Engine API
    Route::get('/siswa/game/balapan-perkalian', [\App\Http\Controllers\Game\GamePlayController::class, 'mathRacing'])->name('student.game.math-racing');
    Route::post('/api/game/session/start', [\App\Http\Controllers\Game\GamePlayController::class, 'startSession'])->name('game.session.start');
    Route::post('/api/game/session/submit-answer', [\App\Http\Controllers\Game\GamePlayController::class, 'submitAnswer'])->name('game.session.submit');
    Route::post('/api/game/session/finish', [\App\Http\Controllers\Game\GamePlayController::class, 'finishSession'])->name('game.session.finish');
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

    // Bank Soal
    Route::get('/guru/soal', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'index'])->name('teacher.questions.index');
    Route::get('/guru/soal/create', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'create'])->name('teacher.questions.create');
    Route::post('/guru/soal', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'store'])->name('teacher.questions.store');
    Route::get('/guru/soal/{id}/edit', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'edit'])->name('teacher.questions.edit');
    Route::put('/guru/soal/{id}', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'update'])->name('teacher.questions.update');
    Route::delete('/guru/soal/{id}', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'destroy'])->name('teacher.questions.destroy');
    Route::post('/guru/soal/import-csv', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'importCsv'])->name('teacher.questions.import');
    Route::get('/guru/soal/template-csv', [\App\Http\Controllers\Teacher\QuestionBankController::class, 'downloadTemplate'])->name('teacher.questions.template');

    Route::post('/guru/logout', [TeacherAuthController::class, 'logout'])->name('teacher.logout');
});
