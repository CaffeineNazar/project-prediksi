<?php
// app/Http/Resources/PrediksiResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrediksiResource extends JsonResource
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
            'tanggal_prediksi' => $this->tanggal_prediksi->format('Y-m-d'),
            'semester_prediksi' => $this->semester_prediksi,
            'hasil_prediksi' => $this->hasil_prediksi,
            'hasil_prediksi_label' => $this->hasil_prediksi_label,
            'probabilitas' => (float) $this->probabilitas,
            'probabilitas_persen' => $this->probabilitas_persen,
            'tingkat_risiko' => $this->tingkat_risiko,
            'risiko_color' => $this->risiko_color,
            'risiko_icon' => $this->risiko_icon,
            'features' => [
                'ip_rata_rata' => (float) $this->ip_rata_rata,
                'ip_trend' => (float) $this->ip_trend,
                'ip_std' => (float) $this->ip_std,
            ],
            'model_version' => $this->model_version,
            'catatan' => $this->catatan,
            'created_by' => $this->when($this->creator, [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ]),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}