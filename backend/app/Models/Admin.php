<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'entity_id',
        'entity_type',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // --- Relationships ---

    public function sppgProvider(): BelongsTo
    {
        return $this->belongsTo(SppgProvider::class, 'entity_id')
            ->where('entity_type', 'sppg_providers');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'entity_id')
            ->where('entity_type', 'schools');
    }

    // --- Scopes ---

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    // --- Helpers ---

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminSppg(): bool
    {
        return $this->role === 'admin_sppg';
    }

    public function isAdminSchool(): bool
    {
        return $this->role === 'admin_school';
    }
}
