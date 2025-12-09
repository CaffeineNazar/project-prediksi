<?php
// app/Http/Controllers/API/IntervensiController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\IntervensiRequest;
use App\Http\Resources\IntervensiResource;
use App\Services\IntervensiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntervensiController extends Controller
{
    protected IntervensiService $intervensiService;

    public function __construct(IntervensiService $intervensiService)
    {
        $this->intervensiService = $intervensiService;
    }

    /**
     * Get all intervensi
     * GET /api/intervensi
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'jenis', 'pic_dosen_id', 'mahasiswa_id', 'per_page']);
            $intervensi = $this->intervensiService->getIntervensi($filters);

            return response()->json([
                'success' => true,
                'data' => IntervensiResource::collection($intervensi),
                'meta' => [
                    'current_page' => $intervensi->currentPage(),
                    'last_page' => $intervensi->lastPage(),
                    'per_page' => $intervensi->perPage(),
                    'total' => $intervensi->total(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data intervensi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create intervensi
     * POST /api/intervensi
     */
    public function store(IntervensiRequest $request): JsonResponse
    {
        try {
            $intervensi = $this->intervensiService->createIntervensi(
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Intervensi berhasil dibuat',
                'data' => new IntervensiResource($intervensi),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat intervensi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update intervensi status
     * PATCH /api/intervensi/{id}/status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:planned,ongoing,completed,cancelled',
            'hasil' => 'nullable|string',
        ]);

        try {
            $intervensi = $this->intervensiService->updateStatus(
                $id,
                $request->status,
                $request->hasil
            );

            return response()->json([
                'success' => true,
                'message' => 'Status intervensi berhasil diupdate',
                'data' => new IntervensiResource($intervensi),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}