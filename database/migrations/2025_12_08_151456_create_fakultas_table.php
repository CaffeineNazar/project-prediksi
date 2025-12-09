<?php
// database/migrations/2024_01_01_000001_create_fakultas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fakultas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_fakultas', 10)->unique();
            $table->string('nama_fakultas');
            $table->timestamps();
            
            // Indexes
            $table->index('kode_fakultas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fakultas');
    }
};