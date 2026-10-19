<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'requested_by',
        'request_type',
        'status',
        'deadline_at',
        'notes',
        'file_path',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'deadline_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
