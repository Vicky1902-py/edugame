<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WordBlacklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'word',
        'severity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function getActiveWords(): array
    {
        return Cache::remember('word_blacklist.active', 3600, function () {
            return static::where('is_active', true)->pluck('word')->toArray();
        });
    }

    public static function containsForbiddenWord(string $text): bool
    {
        $lowered = strtolower($text);
        foreach (static::getActiveWords() as $badWord) {
            if ($badWord !== '' && str_contains($lowered, strtolower($badWord))) {
                return true;
            }
        }

        return false;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('word_blacklist.active'));
        static::deleted(fn () => Cache::forget('word_blacklist.active'));
    }
}
