<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'pin_hash',
        'school_id',
        'last_login_at',
        'last_login_ip',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'pin_hash',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->hasRole('superadmin');
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole('superadmin') || $this->role === 'admin';
    }

    /**
     * Otorisasi akses ke Filament Panel.
     * Hanya pengguna dengan role superadmin/admin dan status active yang diizinkan.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->isSuperadmin() && $this->status === 'active';
        }

        return false;
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            if ($user->role === 'admin' || $user->hasRole('superadmin')) {
                $superadminCount = User::where('role', 'admin')
                    ->orWhereHas('roles', fn ($q) => $q->where('name', 'superadmin'))
                    ->count();

                if ($superadminCount <= 1) {
                    throw new \RuntimeException('Superadmin terakhir tidak dapat dihapus untuk mencegah hilangnya kendali platform.');
                }
            }
        });

        static::updating(function (User $user) {
            if ($user->isDirty('role') && $user->getOriginal('role') === 'admin' && $user->role !== 'admin') {
                $superadminCount = User::where('role', 'admin')->count();
                if ($superadminCount <= 1) {
                    throw new \RuntimeException('Peran Superadmin terakhir tidak dapat diturunkan.');
                }
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function taughtClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'guru_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function enrolledClasses(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'enrollments', 'student_id', 'class_id')
                    ->withTimestamps();
    }

    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class, 'student_id');
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges', 'user_id', 'badge_id')
                    ->withPivot('diperoleh_pada')
                    ->withTimestamps();
    }

    public function userBadges(): HasMany
    {
        return $this->hasMany(UserBadge::class, 'user_id');
    }

    public function xpLedger(): HasMany
    {
        return $this->hasMany(XpLedger::class, 'user_id');
    }

    public function totalXp(): int
    {
        return (int) $this->xpLedger()->sum('jumlah_xp');
    }
}
