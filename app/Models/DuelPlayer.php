<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuelPlayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'student_id',
        'score',
        'streak',
        'last_active_at',
        'is_connected',
        'joined_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'streak' => 'integer',
        'is_connected' => 'boolean',
        'last_active_at' => 'datetime',
        'joined_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(DuelRoom::class, 'room_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
