<?php
// app/Models/Mahasiswa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'nama',
        'program_studi_id',
        'tahun_masuk',
        'status_mahasiswa',
    ];

    protected $casts = [
        'tahun_masuk' => 'integer',
        'status_mahasiswa' => 'string',
    ];

    // Relationships
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function nilaiSemester(): HasMany
    {
        return $this->hasMany(NilaiSemester::class)->orderBy('semester');
    }

    public function prediksiKelulusan(): HasMany
    {
        return $this->hasMany(PrediksiKelulusan::class)->latest('tanggal_prediksi');
    }

    public function intervensiAkademik(): HasMany
    {
        return $this->hasMany(IntervensiAkademik::class);
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusMahasiswa::class)->latest();
    }

    // Accessor
    public function getIpkTerakhirAttribute(): ?float
    {
        return $this->nilaiSemester()->latest('semester')->first()?->ipk;
    }

    public function getSemesterAktifAttribute(): ?int
    {
        return $this->nilaiSemester()->max('semester');
    }

    public function getPrediksiTerakhirAttribute()
    {
        return $this->prediksiKelulusan()->first();
    }

    // Scope
    public function scopeAktif($query)
    {
        return $query->where('status_mahasiswa', 'aktif');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status_mahasiswa', $status);
    }

    public function scopeByTahunMasuk($query, int $tahun)
    {
        return $query->where('tahun_masuk', $tahun);
    }

    public function scopeByProdi($query, int $prodiId)
    {
        return $query->where('program_studi_id', $prodiId);
    }
}