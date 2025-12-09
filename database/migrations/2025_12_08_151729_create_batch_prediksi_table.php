<?php
// database/migrations/2024_01_01_000006_create_batch_prediksi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_prediksi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_batch')->nullable();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->integer('total_mahasiswa')->default(0);
            $table->integer('total_berhasil')->default(0);
            $table->integer('total_gagal')->default(0);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_prediksi');
    }
};