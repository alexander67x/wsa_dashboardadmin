<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'failed_login_attempts',
        'last_failed_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_failed_login_at' => 'datetime',
        ];
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'user_id', 'id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function permissionCodes(): array
    {
        return $this->empleado?->permissionCodes() ?? [];
    }

    public function hasPermission(string $permission): bool
    {
        return $this->empleado?->hasPermission($permission) ?? false;
    }

    /**
     * Controla si el usuario puede acceder al panel de Filament.
     * De momento permitimos acceso a cualquier usuario autenticado.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
