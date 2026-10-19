<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XpLedger extends Model
{
    use HasFactory;

    protected $table = 'xp_ledger';

    protected $fillable = [
        'user_id',
        'causer_id',
        'jumlah_xp',
        'balance_after',
        'sumber',
        'reason',
    ];

    protected $casts = [
        'jumlah_xp' => 'integer',
        'balance_after' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
