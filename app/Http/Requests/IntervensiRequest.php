<?php
// app/Http/Requests/IntervensiRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IntervensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prediksi_kelulusan_id' => 'required|exists:prediksi_kelulusan,id',
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'jenis_intervensi' => 'required|in:konseling,remedial,mentoring,lainnya',
            'deskripsi' => 'required|string|min:10',
            'tanggal_intervensi' => 'required|date|after_or_equal:today',
            'pic_dosen_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'prediksi_kelulusan_id.required' => 'ID Prediksi wajib diisi',
            'mahasiswa_id.required' => 'ID Mahasiswa wajib diisi',
            'jenis_intervensi.required' => 'Jenis intervensi wajib dipilih',
            'deskripsi.required' => 'Deskripsi wajib diisi',
            'deskripsi.min' => 'Deskripsi minimal 10 karakter',
            'tanggal_intervensi.required' => 'Tanggal intervensi wajib diisi',
            'tanggal_intervensi.after_or_equal' => 'Tanggal intervensi tidak boleh di masa lalu',
        ];
    }
}