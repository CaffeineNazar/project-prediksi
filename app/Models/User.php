<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'fakultas_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationships
    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function prediksiCreated(): HasMany
    {
        return $this->hasMany(PrediksiKelulusan::class, 'created_by');
    }

    public function batchCreated(): HasMany
    {
        return $this->hasMany(BatchPrediksi::class, 'created_by');
    }

    public function intervensiAsPic(): HasMany
    {
        return $this->hasMany(IntervensiAkademik::class, 'pic_dosen_id');
    }

    public function intervensiCreated(): HasMany
    {
        return $this->hasMany(IntervensiAkademik::class, 'created_by');
    }

    // Scope
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeDosen($query)
    {
        return $query->where('role', 'dosen');
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    // Helper
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }
}