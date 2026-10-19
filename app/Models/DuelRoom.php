<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DuelRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'class_id',
        'created_by_student_id',
        'is_teacher_session',
        'status',
        'current_question_index',
        'question_ids',
        'question_started_at',
        'expires_at',
    ];

    protected $casts = [
        'is_teacher_session' => 'boolean',
        'current_question_index' => 'integer',
        'question_ids' => 'array',
        'question_started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function createdByStudent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_student_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(DuelPlayer::class, 'room_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DuelEvent::class, 'room_id');
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->expires_at && now()->isAfter($this->expires_at));
    }
}
