<?php
// database/migrations/2024_01_01_000011_add_fakultas_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'dosen', 'operator'])->default('operator')->after('password');
            $table->foreignId('fakultas_id')->nullable()->after('role')->constrained('fakultas')->onDelete('set null');
            
            // Index
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fakultas_id']);
            $table->dropColumn(['role', 'fakultas_id']);
        });
    }
};