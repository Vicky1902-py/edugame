<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'question_id',
        'jawaban_siswa',
        'benar',
        'waktu_ms',
        'poin_didapat',
    ];

    protected $casts = [
        'benar' => 'boolean',
        'waktu_ms' => 'integer',
        'poin_didapat' => 'integer',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(GameSession::class, 'session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
