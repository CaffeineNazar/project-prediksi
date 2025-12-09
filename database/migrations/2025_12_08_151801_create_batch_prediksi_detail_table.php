<?php
// database/migrations/2024_01_01_000007_create_batch_prediksi_detail_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_prediksi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_prediksi_id')->constrained('batch_prediksi')->onDelete('cascade');
            $table->foreignId('prediksi_kelulusan_id')->nullable()->constrained('prediksi_kelulusan')->onDelete('set null');
            $table->string('nim', 20);
            $table->enum('status', ['success', 'error']);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['batch_prediksi_id', 'status']);
            $table->index('nim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_prediksi_detail');
    }
};