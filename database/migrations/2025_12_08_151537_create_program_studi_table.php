<?php
// database/migrations/2024_01_01_000002_create_program_studi_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fakultas_id')->constrained('fakultas')->onDelete('cascade');
            $table->string('kode_prodi', 20)->unique();
            $table->string('nama_prodi');
            $table->enum('jenjang', ['D3', 'S1', 'S2', 'S3']);
            $table->timestamps();
            
            // Indexes
            $table->index(['fakultas_id', 'jenjang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};