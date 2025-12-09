<?php
// app/Models/BatchPrediksi.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchPrediksi extends Model
{
    use HasFactory;

    protected $table = 'batch_prediksi';

    protected $fillable = [
        'nama_batch',
        'file_name',
        'file_path',
        'total_mahasiswa',
        'total_berhasil',
        'total_gagal',
        'status',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'total_mahasiswa' => 'integer',
        'total_berhasil' => 'integer',
        'total_gagal' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BatchPrediksiDetail::class);
    }

    // Accessor
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_mahasiswa == 0) {
            return 0;
        }
        return round(($this->total_berhasil / $this->total_mahasiswa) * 100, 2);
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $diff = $this->started_at->diff($this->completed_at);
        return $diff->format('%H:%I:%S');
    }

    // Scope
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}