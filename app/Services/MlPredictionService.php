<?php
// app/Services/MLPredictionService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Exceptions\MLPredictionException;
use App\Models\ModelMlConfig;

class MLPredictionService
{
    protected string $pythonApiUrl;
    protected int $timeout;

    public function __construct()
    {
        // URL Python API (bisa dari .env)
        $this->pythonApiUrl = config('services.ml_api.url', 'http://localhost:5000');
        $this->timeout = config('services.ml_api.timeout', 30);
    }

    /**
     * Prediksi individual untuk satu mahasiswa
     */
    public function predictIndividual(array $data): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->pythonApiUrl}/api/predict/individual", [
                    'nim' => $data['nim'],
                    'jenjang_prodi' => $data['jenjang_prodi'],
                    'nama_prodi' => $data['nama_prodi'],
                    'fakultas' => $data['fakultas'],
                    'ip_semester_1' => $data['ip_semester_1'],
                    'ip_semester_2' => $data['ip_semester_2'],
                    'ip_semester_3' => $data['ip_semester_3'] ?? 0,
                    'ip_semester_4' => $data['ip_semester_4'] ?? 0,
                ]);

            if ($response->failed()) {
                throw new MLPredictionException(
                    "Prediction API failed: " . $response->body()
                );
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('ML Prediction Error: ' . $e->getMessage());
            throw new MLPredictionException(
                "Failed to get prediction: " . $e->getMessage()
            );
        }
    }

    /**
     * Prediksi batch untuk multiple mahasiswa
     */
    public function predictBatch(array $dataArray): array
    {
        try {
            $response = Http::timeout($this->timeout * 2) // Double timeout for batch
                ->post("{$this->pythonApiUrl}/api/predict/batch", [
                    'data' => $dataArray
                ]);

            if ($response->failed()) {
                throw new MLPredictionException(
                    "Batch prediction API failed: " . $response->body()
                );
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('ML Batch Prediction Error: ' . $e->getMessage());
            throw new MLPredictionException(
                "Failed to get batch prediction: " . $e->getMessage()
            );
        }
    }

    /**
     * Health check Python API
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->pythonApiUrl}/api/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get model info dari Python API
     */
    public function getModelInfo(): ?array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->pythonApiUrl}/api/model/info");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get model info: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate IPK from IP semesters
     */
    public function calculateIPK(array $ipSemesters): array
    {
        $validIPs = array_filter($ipSemesters, fn($ip) => $ip > 0);
        
        if (empty($validIPs)) {
            return [0, 0, 0, 0];
        }

        $ipk = [];
        $cumulative = 0;
        $count = 0;

        foreach ($ipSemesters as $ip) {
            if ($ip > 0) {
                $cumulative += $ip;
                $count++;
                $ipk[] = round($cumulative / $count, 2);
            } else {
                $ipk[] = end($ipk) ?: 0; // Use last IPK if no IP for this semester
            }
        }

        return $ipk;
    }

    /**
     * Calculate aggregate features
     */
    public function calculateAggregateFeatures(array $ipSemesters): array
    {
        $validIPs = array_filter($ipSemesters, fn($ip) => $ip > 0);
        
        if (empty($validIPs)) {
            return [
                'ip_mean' => 0,
                'ip_std' => 0,
                'ip_var' => 0,
                'ip_min' => 0,
                'ip_max' => 0,
                'ip_trend' => 0,
            ];
        }

        $mean = array_sum($validIPs) / count($validIPs);
        $variance = 0;
        
        foreach ($validIPs as $ip) {
            $variance += pow($ip - $mean, 2);
        }
        $variance = $variance / count($validIPs);
        $std = sqrt($variance);

        return [
            'ip_mean' => round($mean, 2),
            'ip_std' => round($std, 2),
            'ip_var' => round($variance, 2),
            'ip_min' => round(min($validIPs), 2),
            'ip_max' => round(max($validIPs), 2),
            'ip_trend' => round(end($validIPs) - reset($validIPs), 2),
        ];
    }

    /**
     * Map prediction result to risk level
     */
    public function mapRiskLevel(string $prediction, float $probability): string
    {
        if ($prediction === 'lulus_tepat_waktu') {
            if ($probability >= 0.7) return 'rendah';
            if ($probability >= 0.5) return 'sedang';
            return 'tinggi';
        } else {
            if ($probability >= 0.7) return 'tinggi';
            if ($probability >= 0.5) return 'sedang';
            return 'rendah';
        }
    }
}