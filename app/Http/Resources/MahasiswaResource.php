<?php
// app/Http/Resources/MahasiswaResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MahasiswaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nim' => $this->nim,
            'nama' => $this->nama,
            'tahun_masuk' => $this->tahun_masuk,
            'status_mahasiswa' => $this->status_mahasiswa,
            'program_studi' => [
                'id' => $this->programStudi->id,
                'kode_prodi' => $this->programStudi->kode_prodi,
                'nama_prodi' => $this->programStudi->nama_prodi,
                'jenjang' => $this->programStudi->jenjang,
                'fakultas' => [
                    'id' => $this->programStudi->fakultas->id,
                    'nama_fakultas' => $this->programStudi->fakultas->nama_fakultas,
                ],
            ],
            'ipk_terakhir' => $this->ipk_terakhir,
            'semester_aktif' => $this->semester_aktif,
            'prediksi_terakhir' => $this->when(
                $this->relationLoaded('prediksiKelulusan') && $this->prediksiKelulusan->isNotEmpty(),
                new PrediksiResource($this->prediksiKelulusan->first())
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}