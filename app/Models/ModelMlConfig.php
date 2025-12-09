<?php
// app/Models/ModelMlConfig.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelMlConfig extends Model
{
    use HasFactory;

    protected $table = 'model_ml_config';

    protected $fillable = [
        'version',
        'model_path',
        'scaler_path',
        'encoders_path',
        'accuracy',
        'precision',
        'recall',
        'f1_score',
        'roc_auc',
        'is_active',
        'trained_date',
    ];

    protected $casts = [
        'accuracy' => 'decimal:4',
        'precision' => 'decimal:4',
        'recall' => 'decimal:4',
        'f1_score' => 'decimal:4',
        'roc_auc' => 'decimal:4',
        'is_active' => 'boolean',
        'trained_date' => 'date',
    ];

    // Scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('trained_date', 'desc');
    }

    // Helper
    public static function getActiveModel()
    {
        return self::where('is_active', true)->first();
    }

    public function activate(): bool
    {
        // Deactivate all other models
        self::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this model
        $this->is_active = true;
        return $this->save();
    }
}