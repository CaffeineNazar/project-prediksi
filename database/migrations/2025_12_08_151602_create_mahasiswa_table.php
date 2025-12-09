<?php
// database/migrations/2024_01_01_000003_create_mahasiswa_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20)->unique();
            $table->string('nama');
            $table->foreignId('program_studi_id')->constrained('program_studi')->onDelete('restrict');
            $table->year('tahun_masuk');
            $table->enum('status_mahasiswa', ['aktif', 'lulus', 'cuti', 'drop_out'])->default('aktif');
            $table->timestamps();
            
            // Indexes
            $table->index(['nim', 'status_mahasiswa']);
            $table->index('tahun_masuk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};