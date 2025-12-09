<?php
// database/migrations/2024_01_01_000005_create_prediksi_kelulusan_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediksi_kelulusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->onDelete('cascade');
            $table->date('tanggal_prediksi');
            $table->tinyInteger('semester_prediksi')->unsigned();
            $table->enum('hasil_prediksi', ['lulus_tepat_waktu', 'berpotensi_terlambat']);
            $table->decimal('probabilitas', 5, 4);
            $table->enum('tingkat_risiko', ['rendah', 'sedang', 'tinggi']);
            $table->decimal('ip_rata_rata', 3, 2)->nullable();
            $table->decimal('ip_trend', 3, 2)->nullable();
            $table->decimal('ip_std', 3, 2)->nullable();
            $table->string('model_version', 20)->default('v1.0');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['mahasiswa_id', 'tanggal_prediksi']);
            $table->index('hasil_prediksi');
            $table->index('tingkat_risiko');
            $table->index('tanggal_prediksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediksi_kelulusan');
    }
};