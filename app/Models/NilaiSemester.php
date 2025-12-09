<?php
// app/Models/NilaiSemester.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NilaiSemester extends Model
{
    use HasFactory;

    protected $table = 'nilai_semester';

    protected $fillable = [
        'mahasiswa_id',
        'semester',
        'ip_semester',
        'ipk',
        'sks_semester',
        'sks_kumulatif',
        'tahun_akademik',
    ];

    protected $casts = [
        'semester' => 'integer',
        'ip_semester' => 'decimal:2',
        'ipk' => 'decimal:2',
        'sks_semester' => 'integer',
        'sks_kumulatif' => 'integer',
    ];

    // Relationships
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    // Scope
    public function scopeBySemester($query, int $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeUpToSemester($query, int $semester)
    {
        return $query->where('semester', '<=', $semester);
    }

    // Helper Methods
    public static function calculateIpkFromSemester(int $mahasiswaId, int $upToSemester): ?float
    {
        $nilai = self::where('mahasiswa_id', $mahasiswaId)
            ->where('semester', '<=', $upToSemester)
            ->get();

        if ($nilai->isEmpty()) {
            return null;
        }

        return round($nilai->avg('ip_semester'), 2);
    }
}