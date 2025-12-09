<?php
// app/Models/BatchPrediksiDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchPrediksiDetail extends Model
{
    use HasFactory;

    protected $table = 'batch_prediksi_detail';

    protected $fillable = [
        'batch_prediksi_id',
        'prediksi_kelulusan_id',
        'nim',
        'status',
        'error_message',
    ];

    // Relationships
    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchPrediksi::class, 'batch_prediksi_id');
    }

    public function prediksi(): BelongsTo
    {
        return $this->belongsTo(PrediksiKelulusan::class, 'prediksi_kelulusan_id');
    }

    // Scope
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeError($query)
    {
        return $query->where('status', 'error');
    }
}