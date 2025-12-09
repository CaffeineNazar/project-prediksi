<?php
// app/Services/PrediksiService.php

namespace App\Services;

use App\Models\PrediksiKelulusan;
use App\Models\Mahasiswa;
use App\Models\NilaiSemester;
use App\Models\BatchPrediksi;
use App\Models\BatchPrediksiDetail;
use App\Exceptions\MLPredictionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrediksiService
{
    protected MLPredictionService $mlService;

    public function __construct(MLPredictionService $mlService)
    {
        $this->mlService = $mlService;
    }

    /**
     * Prediksi individual
     */
    public function predictIndividual(array $data, ?int $userId = null): PrediksiKelulusan
    {
        DB::beginTransaction();
        try {
            // Find or create mahasiswa
            $mahasiswa = $this->findOrCreateMahasiswa($data);

            // Get prediction from ML service
            $mlResult = $this->mlService->predictIndividual($data);

            // Calculate aggregate features
            $ipSemesters = [
                $data['ip_semester_1'],
                $data['ip_semester_2'],
                $data['ip_semester_3'] ?? 0,
                $data['ip_semester_4'] ?? 0,
            ];
            $aggregates = $this->mlService->calculateAggregateFeatures($ipSemesters);

            // Map risk level
            $riskLevel = $this->mlService->mapRiskLevel(
                $mlResult['prediction'],
                $mlResult['probability']
            );

            // Save prediction
            $prediksi = PrediksiKelulusan::create([
                'mahasiswa_id' => $mahasiswa->id,
                'tanggal_prediksi' => now()->toDateString(),
                'semester_prediksi' => $this->determineSemesterPrediksi($ipSemesters),
                'hasil_prediksi' => $mlResult['prediction'],
                'probabilitas' => $mlResult['probability'],
                'tingkat_risiko' => $riskLevel,
                'ip_rata_rata' => $aggregates['ip_mean'],
                'ip_trend' => $aggregates['ip_trend'],
                'ip_std' => $aggregates['ip_std'],
                'model_version' => $mlResult['model_version'] ?? 'v1.0',
                'created_by' => $userId,
            ]);

            DB::commit();
            return $prediksi->load('mahasiswa.programStudi.fakultas');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Prediksi Individual Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prediksi batch dari file upload
     */
    public function predictBatch(array $dataArray, string $fileName, string $filePath, int $userId): BatchPrediksi
    {
        DB::beginTransaction();
        try {
            // Create batch record
            $batch = BatchPrediksi::create([
                'nama_batch' => $fileName,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'total_mahasiswa' => count($dataArray),
                'total_berhasil' => 0,
                'total_gagal' => 0,
                'status' => 'processing',
                'started_at' => now(),
                'created_by' => $userId,
            ]);

            DB::commit();

            // Process predictions (dapat di-queue untuk performa lebih baik)
            $this->processBatchPredictions($batch, $dataArray, $userId);

            return $batch->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch Creation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process batch predictions
     */
    protected function processBatchPredictions(BatchPrediksi $batch, array $dataArray, int $userId): void
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($dataArray as $data) {
            try {
                // Predict individual
                $prediksi = $this->predictIndividual($data, $userId);

                // Create detail record
                BatchPrediksiDetail::create([
                    'batch_prediksi_id' => $batch->id,
                    'prediksi_kelulusan_id' => $prediksi->id,
                    'nim' => $data['nim'],
                    'status' => 'success',
                ]);

                $successCount++;

            } catch (\Exception $e) {
                // Create error detail
                BatchPrediksiDetail::create([
                    'batch_prediksi_id' => $batch->id,
                    'nim' => $data['nim'] ?? 'unknown',
                    'status' => 'error',
                    'error_message' => $e->getMessage(),
                ]);

                $failCount++;
                Log::error("Batch item error for NIM {$data['nim']}: " . $e->getMessage());
            }
        }

        // Update batch status
        $batch->update([
            'total_berhasil' => $successCount,
            'total_gagal' => $failCount,
            'status' => $failCount === 0 ? 'completed' : 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Get predictions by mahasiswa
     */
    public function getPredictionsByMahasiswa(int $mahasiswaId, int $limit = 10)
    {
        return PrediksiKelulusan::where('mahasiswa_id', $mahasiswaId)
            ->with(['creator', 'intervensiAkademik'])
            ->latest('tanggal_prediksi')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest prediction for mahasiswa
     */
    public function getLatestPrediction(int $mahasiswaId): ?PrediksiKelulusan
    {
        return PrediksiKelulusan::where('mahasiswa_id', $mahasiswaId)
            ->latest('tanggal_prediksi')
            ->first();
    }

    /**
     * Find or create mahasiswa
     */
    protected function findOrCreateMahasiswa(array $data): Mahasiswa
    {
        $mahasiswa = Mahasiswa::where('nim', $data['nim'])->first();

        if (!$mahasiswa) {
            // Find program studi
            $prodi = \App\Models\ProgramStudi::where('nama_prodi', $data['nama_prodi'])
                ->whereHas('fakultas', function($q) use ($data) {
                    $q->where('nama_fakultas', $data['fakultas']);
                })
                ->first();

            if (!$prodi) {
                throw new \Exception("Program studi tidak ditemukan: {$data['nama_prodi']}");
            }

            $mahasiswa = Mahasiswa::create([
                'nim' => $data['nim'],
                'nama' => $data['nama'] ?? 'Unknown',
                'program_studi_id' => $prodi->id,
                'tahun_masuk' => $data['tahun_masuk'] ?? now()->year,
                'status_mahasiswa' => 'aktif',
            ]);
        }

        return $mahasiswa;
    }

    /**
     * Determine semester prediksi based on IP data
     */
    protected function determineSemesterPrediksi(array $ipSemesters): int
    {
        $validCount = count(array_filter($ipSemesters, fn($ip) => $ip > 0));
        return max(2, $validCount); // Minimal semester 2
    }

    /**
     * Get statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = PrediksiKelulusan::query();

        // Apply filters
        if (isset($filters['start_date'])) {
            $query->where('tanggal_prediksi', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date'])) {
            $query->where('tanggal_prediksi', '<=', $filters['end_date']);
        }
        if (isset($filters['fakultas_id'])) {
            $query->whereHas('mahasiswa.programStudi', function($q) use ($filters) {
                $q->where('fakultas_id', $filters['fakultas_id']);
            });
        }

        $total = $query->count();
        $lulusTepat = $query->clone()->where('hasil_prediksi', 'lulus_tepat_waktu')->count();
        $berpotensiTerlambat = $query->clone()->where('hasil_prediksi', 'berpotensi_terlambat')->count();
        $risikoTinggi = $query->clone()->where('tingkat_risiko', 'tinggi')->count();

        return [
            'total_prediksi' => $total,
            'lulus_tepat_waktu' => $lulusTepat,
            'berpotensi_terlambat' => $berpotensiTerlambat,
            'risiko_tinggi' => $risikoTinggi,
            'persentase_lulus' => $total > 0 ? round(($lulusTepat / $total) * 100, 2) : 0,
            'persentase_risiko_tinggi' => $total > 0 ? round(($risikoTinggi / $total) * 100, 2) : 0,
        ];
    }
}