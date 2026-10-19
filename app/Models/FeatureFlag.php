<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_key',
        'name',
        'description',
        'is_enabled',
        'rules_json',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'rules_json' => 'array',
        ];
    }

    public static function isEnabled(string $key, bool $default = true): bool
    {
        return Cache::remember("feature_flag.{$key}", 300, function () use ($key, $default) {
            $flag = static::where('feature_key', $key)->first();
            return $flag ? $flag->is_enabled : $default;
        });
    }

    protected static function booted(): void
    {
        static::saved(function (FeatureFlag $flag) {
            Cache::forget("feature_flag.{$flag->feature_key}");
        });

        static::deleted(function (FeatureFlag $flag) {
            Cache::forget("feature_flag.{$flag->feature_key}");
        });
    }
}
