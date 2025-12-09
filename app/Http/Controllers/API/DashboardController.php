<?php
// app/Http/Controllers/API/DashboardController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\PrediksiKelulusan;
use App\Models\IntervensiAkademik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * GET /api/dashboard/stats
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            // Basic counts
            $totalMahasiswa = Mahasiswa::where('status_mahasiswa', 'aktif')->count();
            $totalPrediksi = PrediksiKelulusan::count();
            
            // Latest predictions
            $prediksiTerbaru = PrediksiKelulusan::whereIn('id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('prediksi_kelulusan')
                    ->groupBy('mahasiswa_id');
            })->get();

            $lulusTepat = $prediksiTerbaru->where('hasil_prediksi', 'lulus_tepat_waktu')->count();
            $berpotensiTerlambat = $prediksiTerbaru->where('hasil_prediksi', 'berpotensi_terlambat')->count();
            $risikoTinggi = $prediksiTerbaru->where('tingkat_risiko', 'tinggi')->count();

            // Intervensi stats
            $intervensiAktif = IntervensiAkademik::whereIn('status', ['planned', 'ongoing'])->count();

            // Statistics by fakultas
            $statsByFakultas = PrediksiKelulusan::whereIn('id', function($query) {
                $query->select(DB::raw('MAX(id)'))->from('prediksi_kelulusan')->groupBy('mahasiswa_id');
            }) ->with('mahasiswa.programStudi.fakultas') ->get() ->groupBy('mahasiswa.program_studi.fakultas.nama_fakultas')->map(function($group) {
                return [
                    'total' => $group->count(), 'lulus_tepat' => $group->where('hasil_prediksi', 'lulus_tepat_waktu')->count(), 
                    'berpotensi_terlambat' => $group->where('hasil_prediksi', 'berpotensi_terlambat')->count(),
                    'risiko_tinggi' => $group->where('tingkat_risiko', 'tinggi')->count(),
                ];
            });
            return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_mahasiswa' => $totalMahasiswa,
                    'total_prediksi' => $totalPrediksi,
                    'lulus_tepat_waktu' => $lulusTepat,
                    'berpotensi_terlambat' => $berpotensiTerlambat,
                    'risiko_tinggi' => $risikoTinggi,
                    'intervensi_aktif' => $intervensiAktif,
                ],
                'by_fakultas' => $statsByFakultas,
                'percentage' => [
                    'lulus_tepat' => $prediksiTerbaru->count() > 0 
                        ? round(($lulusTepat / $prediksiTerbaru->count()) * 100, 2) 
                        : 0,
                    'berpotensi_terlambat' => $prediksiTerbaru->count() > 0 
                        ? round(($berpotensiTerlambat / $prediksiTerbaru->count()) * 100, 2) 
                        : 0,
                ],
            ],
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
         * Get recent activities
         * GET /api/dashboard/recent
         */
        public function recentActivities(): JsonResponse
    {
        try {
            $recentPrediksi = PrediksiKelulusan::with('mahasiswa')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($p) {
                    return [
                        'type' => 'prediksi',
                        'mahasiswa' => $p->mahasiswa->nama,
                        'nim' => $p->mahasiswa->nim,
                        'hasil' => $p->hasil_prediksi_label,
                        'timestamp' => $p->created_at->toISOString(),
                    ];
                });

            $recentIntervensi = IntervensiAkademik::with('mahasiswa')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($i) {
                    return [
                        'type' => 'intervensi',
                        'mahasiswa' => $i->mahasiswa->nama,
                        'nim' => $i->mahasiswa->nim,
                        'jenis' => $i->jenis_intervensi,
                        'status' => $i->status,
                        'timestamp' => $i->created_at->toISOString(),
                    ];
                });

            $activities = $recentPrediksi->merge($recentIntervensi)
                ->sortByDesc('timestamp')
                ->take(15)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $activities,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil recent activities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}