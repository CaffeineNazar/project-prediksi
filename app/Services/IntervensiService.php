<?php
// app/Services/IntervensiService.php

namespace App\Services;

use App\Models\IntervensiAkademik;
use App\Models\PrediksiKelulusan;
use Illuminate\Support\Facades\DB;

class IntervensiService
{
    /**
     * Create intervensi
     */
    public function createIntervensi(array $data, int $userId): IntervensiAkademik
    {
        DB::beginTransaction();
        try {
            $intervensi = IntervensiAkademik::create([
                'prediksi_kelulusan_id' => $data['prediksi_kelulusan_id'],
                'mahasiswa_id' => $data['mahasiswa_id'],
                'jenis_intervensi' => $data['jenis_intervensi'],
                'deskripsi' => $data['deskripsi'],
                'tanggal_intervensi' => $data['tanggal_intervensi'],
                'pic_dosen_id' => $data['pic_dosen_id'] ?? null,
                'status' => 'planned',
                'created_by' => $userId,
            ]);

            DB::commit();
            return $intervensi->load(['mahasiswa', 'prediksi', 'picDosen']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update intervensi status
     */
    public function updateStatus(int $id, string $status, ?string $hasil = null): IntervensiAkademik
    {
        $intervensi = IntervensiAkademik::findOrFail($id);
        
        $intervensi->update([
            'status' => $status,
            'hasil' => $hasil,
        ]);

        return $intervensi->fresh();
    }

    /**
     * Get intervensi by filters
     */
    public function getIntervensi(array $filters = [])
    {
        $query = IntervensiAkademik::with([
            'mahasiswa.programStudi.fakultas',
            'prediksi',
            'picDosen',
            'creator'
        ]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['jenis'])) {
            $query->where('jenis_intervensi', $filters['jenis']);
        }
        if (isset($filters['pic_dosen_id'])) {
            $query->where('pic_dosen_id', $filters['pic_dosen_id']);
        }
        if (isset($filters['mahasiswa_id'])) {
            $query->where('mahasiswa_id', $filters['mahasiswa_id']);
        }

        return $query->latest('tanggal_intervensi')->paginate($filters['per_page'] ?? 15);
    }
}