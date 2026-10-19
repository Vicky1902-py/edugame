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
        'jumlah_xp',
        'sumber',
    ];

    protected $casts = [
        'jumlah_xp' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
