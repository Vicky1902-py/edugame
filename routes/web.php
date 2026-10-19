<?php

use App\Models\Question;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
