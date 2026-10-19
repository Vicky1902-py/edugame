<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'game_id',
        'class_id',
        'mulai',
        'selesai',
        'skor',
        'akurasi',
        'total_benar',
        'total_soal',
    ];

    protected $casts = [
        'mulai' => 'datetime',
        'selesai' => 'datetime',
        'akurasi' => 'decimal:2',
        'skor' => 'integer',
        'total_benar' => 'integer',
        'total_soal' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class, 'session_id');
    }
}
