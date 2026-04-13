<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
        'dni', 'phone', 'position',
        'unit_id', 'avatar', 'google_id',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'owner_id');
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->map(fn($w) => strtoupper($w[0]))
            ->take(2)
            ->implode('');
    }
}