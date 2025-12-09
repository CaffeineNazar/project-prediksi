<?php
// app/Http/Controllers/API/MahasiswaController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MahasiswaResource;
use App\Services\MahasiswaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    protected MahasiswaService $mahasiswaService;

    public function __construct(MahasiswaService $mahasiswaService)
    {
        $this->mahasiswaService = $mahasiswaService;
    }

    /**
     * Get all mahasiswa with filters
     * GET /api/mahasiswa
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'prodi_id', 'tahun_masuk', 'search', 'per_page']);
            $mahasiswa = $this->mahasiswaService->getMahasiswa($filters);

            return response()->json([
                'success' => true,
                'data' => MahasiswaResource::collection($mahasiswa),
                'meta' => [
                    'current_page' => $mahasiswa->currentPage(),
                    'last_page' => $mahasiswa->lastPage(),
                    'per_page' => $mahasiswa->perPage(),
                    'total' => $mahasiswa->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data mahasiswa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get mahasiswa detail
     * GET /api/mahasiswa/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $mahasiswa = $this->mahasiswaService->getMahasiswaDetail($id);

            return response()->json([
                'success' => true,
                'data' => new MahasiswaResource($mahasiswa),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get mahasiswa berisiko
     * GET /api/mahasiswa/berisiko
     */
    public function berisiko(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['tingkat_risiko', 'per_page']);
            $mahasiswa = $this->mahasiswaService->getMahasiswaBerisiko($filters);

            return response()->json([
                'success' => true,
                'data' => MahasiswaResource::collection($mahasiswa),
                'meta' => [
                    'current_page' => $mahasiswa->currentPage(),
                    'last_page' => $mahasiswa->lastPage(),
                    'per_page' => $mahasiswa->perPage(),
                    'total' => $mahasiswa->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data mahasiswa berisiko',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}