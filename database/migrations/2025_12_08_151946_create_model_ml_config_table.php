<?php
// database/migrations/2024_01_01_000010_create_model_ml_config_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_ml_config', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20)->unique();
            $table->string('model_path', 500);
            $table->string('scaler_path', 500);
            $table->string('encoders_path', 500);
            $table->decimal('accuracy', 5, 4)->nullable();
            $table->decimal('precision', 5, 4)->nullable();
            $table->decimal('recall', 5, 4)->nullable();
            $table->decimal('f1_score', 5, 4)->nullable();
            $table->decimal('roc_auc', 5, 4)->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('trained_date')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_ml_config');
    }
};