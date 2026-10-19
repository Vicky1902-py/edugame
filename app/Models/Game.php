<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_slug',
        'nama',
        'jenjang',
        'konfigurasi_json',
    ];

    protected $casts = [
        'konfigurasi_json' => 'array',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'game_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'game_id');
    }
}
