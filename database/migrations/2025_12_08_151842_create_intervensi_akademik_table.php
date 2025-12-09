<?php
// database/migrations/2024_01_01_000008_create_intervensi_akademik_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervensi_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediksi_kelulusan_id')->constrained('prediksi_kelulusan')->onDelete('cascade');
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->enum('jenis_intervensi', ['konseling', 'remedial', 'mentoring', 'lainnya']);
            $table->text('deskripsi');
            $table->date('tanggal_intervensi');
            $table->foreignId('pic_dosen_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['planned', 'ongoing', 'completed', 'cancelled'])->default('planned');
            $table->text('hasil')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes
            $table->index(['mahasiswa_id', 'status']);
            $table->index('tanggal_intervensi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervensi_akademik');
    }
};