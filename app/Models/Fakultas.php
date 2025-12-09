<?php
// app/Models/Fakultas.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas';

    protected $fillable = [
        'kode_fakultas',
        'nama_fakultas',
    ];

    // Relationships
    public function programStudi(): HasMany
    {
        return $this->hasMany(ProgramStudi::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Accessor
    public function getJumlahProdiAttribute(): int
    {
        return $this->programStudi()->count();
    }

    // Scope
    public function scopeWithProdiCount($query)
    {
        return $query->withCount('programStudi');
    }
}