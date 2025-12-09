<?php
// database/migrations/2025_12_08_151626_create_nilai_semester_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // [PENTING] Tambahkan import ini

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
            
            // HAPUS atau KOMENTARI baris-baris ini karena menyebabkan error:
            // $table->check('ip_semester >= 0 AND ip_semester <= 4');
            // $table->check('ipk >= 0 AND ipk <= 4');
            // $table->check('semester >= 1 AND semester <= 14');
        });

        // [SOLUSI] Tambahkan Check Constraint menggunakan Raw SQL di sini
        // Pastikan database Anda (MySQL 8.0.16+ atau MariaDB 10.2.1+) mendukung CHECK constraint.
        DB::statement('ALTER TABLE nilai_semester ADD CONSTRAINT chk_ip_semester CHECK (ip_semester >= 0 AND ip_semester <= 4)');
        DB::statement('ALTER TABLE nilai_semester ADD CONSTRAINT chk_ipk CHECK (ipk >= 0 AND ipk <= 4)');
        DB::statement('ALTER TABLE nilai_semester ADD CONSTRAINT chk_semester CHECK (semester >= 1 AND semester <= 14)');
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_semester');
    }
};