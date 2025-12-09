<?php
// database/migrations/2024_01_01_000004_create_nilai_semester_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_semester', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->tinyInteger('semester')->unsigned();
            $table->decimal('ip_semester', 3, 2);
            $table->decimal('ipk', 3, 2);
            $table->tinyInteger('sks_semester')->unsigned()->nullable();
            $table->smallInteger('sks_kumulatif')->unsigned()->nullable();
            $table->string('tahun_akademik', 10)->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['mahasiswa_id', 'semester']);
            
            // Indexes
            $table->index('semester');
            $table->index('tahun_akademik');
            
            // Check constraints (Laravel 10+)
            $table->check('ip_semester >= 0 AND ip_semester <= 4');
            $table->check('ipk >= 0 AND ipk <= 4');
            $table->check('semester >= 1 AND semester <= 14');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_semester');
    }
};