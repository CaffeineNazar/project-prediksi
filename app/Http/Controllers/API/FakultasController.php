<?php
// app/Http/Controllers/API/FakultasController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use Illuminate\Http\JsonResponse;

class FakultasController extends Controller
{
    /**
     * Get all fakultas
     * GET /api/fakultas
     */
    public function index(): JsonResponse
    {
        try {
            $fakultas = Fakultas::withCount('programStudi')->get();

            return response()->json([
                'success' => true,
                'data' => $fakultas->map(function($fak) {
                    return [
                        'id' => $fak->id,
                        'kode_fakultas' => $fak->kode_fakultas,
                        'nama_fakultas' => $fak->nama_fakultas,
                        'jumlah_prodi' => $fak->program_studi_count,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data fakultas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get fakultas with prodi
     * GET /api/fakultas/{id}/prodi
     */
    public function withProdi(int $id): JsonResponse
    {
        try {
            $fakultas = Fakultas::with('programStudi')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $fakultas->id,
                    'kode_fakultas' => $fakultas->kode_fakultas,
                    'nama_fakultas' => $fakultas->nama_fakultas,
                    'program_studi' => $fakultas->programStudi->map(function($prodi) {
                        return [
                            'id' => $prodi->id,
                            'kode_prodi' => $prodi->kode_prodi,
                            'nama_prodi' => $prodi->nama_prodi,
                            'jenjang' => $prodi->jenjang,
                        ];
                    }),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fakultas tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}