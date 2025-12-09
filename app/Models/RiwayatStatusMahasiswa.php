<?php
// app/Models/RiwayatStatusMahasiswa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'riwayat_status_mahasiswa';

    protected $fillable = [
        'mahasiswa_id',
        'status_lama',
        'status_baru',
        'semester',
        'keterangan',
        'changed_by',
    ];

    protected $casts = [
        'semester' => 'integer',
    ];

    // Relationships
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scope
    public function scopeByMahasiswa($query, int $mahasiswaId)
    {
        return $query->where('mahasiswa_id', $mahasiswaId);
    }
}