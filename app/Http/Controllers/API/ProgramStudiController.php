<?php
// app/Http/Controllers/API/ProgramStudiController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramStudiController extends Controller
{
    /**
     * Get all program studi
     * GET /api/program-studi
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ProgramStudi::with('fakultas');

            if ($request->has('fakultas_id')) {
                $query->where('fakultas_id', $request->fakultas_id);
            }

            if ($request->has('jenjang')) {
                $query->where('jenjang', $request->jenjang);
            }

            $prodi = $query->get();

            return response()->json([
                'success' => true,
                'data' => $prodi->map(function($p) {
                    return [
                        'id' => $p->id,
                        'kode_prodi' => $p->kode_prodi,
                        'nama_prodi' => $p->nama_prodi,
                        'jenjang' => $p->jenjang,
                        'fakultas' => [
                            'id' => $p->fakultas->id,
                            'nama_fakultas' => $p->fakultas->nama_fakultas,
                        ],
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data program studi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}