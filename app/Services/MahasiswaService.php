<?php
// app/Services/MahasiswaService.php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\NilaiSemester;
use Illuminate\Support\Facades\DB;

class MahasiswaService
{
    /**
     * Get mahasiswa with filters
     */
    public function getMahasiswa(array $filters = [])
    {
        $query = Mahasiswa::with(['programStudi.fakultas', 'prediksiKelulusan' => function($q) {
            $q->latest('tanggal_prediksi')->limit(1);
        }]);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status_mahasiswa', $filters['status']);
        }
        if (isset($filters['prodi_id'])) {
            $query->where('program_studi_id', $filters['prodi_id']);
        }
        if (isset($filters['tahun_masuk'])) {
            $query->where('tahun_masuk', $filters['tahun_masuk']);
        }
        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('nim', 'like', "%{$filters['search']}%")
                  ->orWhere('nama', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get mahasiswa detail with complete data
     */
    public function getMahasiswaDetail(int $id)
    {
        return Mahasiswa::with([
            'programStudi.fakultas',
            'nilaiSemester' => function($q) {
                $q->orderBy('semester');
            },
            'prediksiKelulusan' => function($q) {
                $q->latest('tanggal_prediksi')->limit(5);
            },
            'intervensiAkademik.picDosen',
            'riwayatStatus'
        ])->findOrFail($id);
    }

    /**
     * Create or update nilai semester
     */
    public function saveNilaiSemester(int $mahasiswaId, array $nilaiData): NilaiSemester
    {
        return NilaiSemester::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswaId,
                'semester' => $nilaiData['semester'],
            ],
            [
                'ip_semester' => $nilaiData['ip_semester'],
                'ipk' => $nilaiData['ipk'],
                'sks_semester' => $nilaiData['sks_semester'] ?? null,
                'sks_kumulatif' => $nilaiData['sks_kumulatif'] ?? null,
                'tahun_akademik' => $nilaiData['tahun_akademik'] ?? null,
            ]
        );
    }

    /**
     * Get mahasiswa at risk (latest prediction is berpotensi_terlambat)
     */
    public function getMahasiswaBerisiko(array $filters = [])
    {
        $query = Mahasiswa::whereHas('prediksiKelulusan', function($q) {
            $q->where('hasil_prediksi', 'berpotensi_terlambat')
              ->whereIn('id', function($subQuery) {
                  $subQuery->select(DB::raw('MAX(id)'))
                      ->from('prediksi_kelulusan')
                      ->groupBy('mahasiswa_id');
              });
        })->with(['programStudi.fakultas', 'prediksiKelulusan' => function($q) {
            $q->latest('tanggal_prediksi')->limit(1);
        }]);

        if (isset($filters['tingkat_risiko'])) {
            $query->whereHas('prediksiKelulusan', function($q) use ($filters) {
                $q->where('tingkat_risiko', $filters['tingkat_risiko'])
                  ->latest('tanggal_prediksi')
                  ->limit(1);
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }
}