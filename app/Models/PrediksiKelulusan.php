<?php
// app/Models/PrediksiKelulusan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrediksiKelulusan extends Model
{
    use HasFactory;

    protected $table = 'prediksi_kelulusan';

    protected $fillable = [
        'mahasiswa_id',
        'tanggal_prediksi',
        'semester_prediksi',
        'hasil_prediksi',
        'probabilitas',
        'tingkat_risiko',
        'ip_rata_rata',
        'ip_trend',
        'ip_std',
        'model_version',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_prediksi' => 'date',
        'semester_prediksi' => 'integer',
        'probabilitas' => 'decimal:4',
        'ip_rata_rata' => 'decimal:2',
        'ip_trend' => 'decimal:2',
        'ip_std' => 'decimal:2',
    ];

    // Relationships
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function intervensiAkademik(): HasMany
    {
        return $this->hasMany(IntervensiAkademik::class);
    }

    public function batchDetail(): HasMany
    {
        return $this->hasMany(BatchPrediksiDetail::class);
    }

    // Accessor
    public function getProbabilitasPersenAttribute(): string
    {
        return number_format($this->probabilitas * 100, 1) . '%';
    }

    public function getHasilPrediksiLabelAttribute(): string
    {
        return $this->hasil_prediksi === 'lulus_tepat_waktu' 
            ? 'Lulus Tepat Waktu' 
            : 'Berpotensi Terlambat Lulus';
    }

    public function getRisikoColorAttribute(): string
    {
        return match($this->tingkat_risiko) {
            'rendah' => 'success',
            'sedang' => 'warning',
            'tinggi' => 'danger',
            default => 'secondary'
        };
    }

    public function getRisikoIconAttribute(): string
    {
        return match($this->tingkat_risiko) {
            'rendah' => '🟢',
            'sedang' => '🟡',
            'tinggi' => '🔴',
            default => '⚪'
        };
    }

    // Scope
    public function scopeBerisiko($query)
    {
        return $query->where('hasil_prediksi', 'berpotensi_terlambat');
    }

    public function scopeRisikoTinggi($query)
    {
        return $query->where('tingkat_risiko', 'tinggi');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_prediksi', [$startDate, $endDate]);
    }
}