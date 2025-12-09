<?php
// app/Http/Controllers/API/PrediksiController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrediksiIndividualRequest;
use App\Http\Requests\PrediksiArrayRequest;
use App\Http\Resources\PrediksiResource;
use App\Services\PrediksiService;
use App\Services\MLPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PrediksiController extends Controller
{
    protected PrediksiService $prediksiService;
    protected MLPredictionService $mlService;

    public function __construct(
        PrediksiService $prediksiService,
        MLPredictionService $mlService
    ) {
        $this->prediksiService = $prediksiService;
        $this->mlService = $mlService;
    }

    /**
     * Prediksi individual
     * POST /api/prediksi/individual
     */
    public function individual(PrediksiIndividualRequest $request): JsonResponse
    {
        try {
            $prediksi = $this->prediksiService->predictIndividual(
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Prediksi berhasil',
                'data' => new PrediksiResource($prediksi),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan prediksi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Prediksi batch dari file Excel
     * POST /api/prediksi/batch
     */
    public function batch(PrediksiArrayRequest $request): JsonResponse
    {
        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads/batch', $fileName, 'public');

            // Read Excel file
            $data = Excel::toArray([], $file);
            $rows = $data[0]; // First sheet
            
            // Remove header
            array_shift($rows);

            // Transform to array of objects
            $dataArray = array_map(function($row) {
                return [
                    'nim' => $row[0] ?? '',
                    'nama' => $row[1] ?? '',
                    'jenjang_prodi' => $row[2] ?? '',
                    'fakultas' => $row[3] ?? '',
                    'nama_prodi' => $row[4] ?? '',
                    'ip_semester_1' => $row[5] ?? 0,
                    'ip_semester_2' => $row[6] ?? 0,
                    'ip_semester_3' => $row[7] ?? 0,
                    'ip_semester_4' => $row[8] ?? 0,
                ];
            }, $rows);

            // Process batch
            $batch = $this->prediksiService->predictBatch(
                $dataArray,
                $fileName,
                $filePath,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Batch prediksi sedang diproses',
                'data' => [
                    'batch_id' => $batch->id,
                    'total_mahasiswa' => $batch->total_mahasiswa,
                    'status' => $batch->status,
                ],
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses batch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get batch status
     * GET /api/prediksi/batch/{id}
     */
    public function batchStatus(int $id): JsonResponse
    {
        try {
            $batch = \App\Models\BatchPrediksi::with(['details.prediksi', 'creator'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $batch->id,
                    'nama_batch' => $batch->nama_batch,
                    'file_name' => $batch->file_name,
                    'total_mahasiswa' => $batch->total_mahasiswa,
                    'total_berhasil' => $batch->total_berhasil,
                    'total_gagal' => $batch->total_gagal,
                    'status' => $batch->status,
                    'success_rate' => $batch->success_rate,
                    'duration' => $batch->duration,
                    'started_at' => $batch->started_at?->toISOString(),
                    'completed_at' => $batch->completed_at?->toISOString(),
                    'created_by' => [
                        'id' => $batch->creator->id,
                        'name' => $batch->creator->name,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Batch tidak ditemukan',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get prediksi history for mahasiswa
     * GET /api/prediksi/mahasiswa/{mahasiswaId}
     */
    public function history(int $mahasiswaId): JsonResponse
    {
        try {
            $prediksi = $this->prediksiService->getPredictionsByMahasiswa($mahasiswaId);

            return response()->json([
                'success' => true,
                'data' => PrediksiResource::collection($prediksi),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics
     * GET /api/prediksi/statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['start_date', 'end_date', 'fakultas_id']);
            $stats = $this->prediksiService->getStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check ML API health
     * GET /api/prediksi/health
     */
    public function health(): JsonResponse
    {
        $isHealthy = $this->mlService->healthCheck();

        return response()->json([
            'success' => true,
            'ml_api_status' => $isHealthy ? 'healthy' : 'unhealthy',
            'model_info' => $isHealthy ? $this->mlService->getModelInfo() : null,
        ]);
    }
}