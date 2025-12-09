<?php
// app/Models/IntervensiAkademik.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntervensiAkademik extends Model
{
    use HasFactory;

    protected $table = 'intervensi_akademik';

    protected $fillable = [
        'prediksi_kelulusan_id',
        'mahasiswa_id',
        'jenis_intervensi',
        'deskripsi',
        'tanggal_intervensi',
        'pic_dosen_id',
        'status',
        'hasil',
        'created_by',
    ];

    protected $casts = [
        'tanggal_intervensi' => 'date',
    ];

    // Relationships
    public function prediksi(): BelongsTo
    {
        return $this->belongsTo(PrediksiKelulusan::class, 'prediksi_kelulusan_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function picDosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_dosen_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scope
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByJenis($query, string $jenis)
    {
        return $query->where('jenis_intervensi', $jenis);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_intervensi', '>=', now()->toDateString())
            ->whereIn('status', ['planned', 'ongoing']);
    }
}