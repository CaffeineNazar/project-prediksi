<?php
// app/Http/Resources/IntervensiResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IntervensiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mahasiswa' => [
                'id' => $this->mahasiswa->id,
                'nim' => $this->mahasiswa->nim,
                'nama' => $this->mahasiswa->nama,
            ],
            'prediksi' => [
                'id' => $this->prediksi->id,
                'hasil_prediksi' => $this->prediksi->hasil_prediksi_label,
                'tingkat_risiko' => $this->prediksi->tingkat_risiko,
            ],
            'jenis_intervensi' => $this->jenis_intervensi,
            'deskripsi' => $this->deskripsi,
            'tanggal_intervensi' => $this->tanggal_intervensi->format('Y-m-d'),
            'status' => $this->status,
            'hasil' => $this->hasil,
            'pic_dosen' => $this->when($this->picDosen, [
                'id' => $this->picDosen?->id,
                'name' => $this->picDosen?->name,
            ]),
            'created_by' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}