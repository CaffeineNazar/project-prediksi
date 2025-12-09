<?php
// app/Http/Requests/PrediksiIndividualRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrediksiIndividualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Implement your authorization logic
    }

    public function rules(): array
    {
        return [
            'nim' => 'required|string|max:20',
            'nama' => 'nullable|string|max:255',
            'jenjang_prodi' => 'required|in:D3,S1,S2,S3',
            'nama_prodi' => 'required|string|max:255',
            'fakultas' => 'required|string|max:255',
            'tahun_masuk' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'ip_semester_1' => 'required|numeric|min:0|max:4',
            'ip_semester_2' => 'required|numeric|min:0|max:4',
            'ip_semester_3' => 'nullable|numeric|min:0|max:4',
            'ip_semester_4' => 'nullable|numeric|min:0|max:4',
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi',
            'jenjang_prodi.required' => 'Jenjang program studi wajib dipilih',
            'jenjang_prodi.in' => 'Jenjang harus D3, S1, S2, atau S3',
            'ip_semester_1.required' => 'IP Semester 1 wajib diisi',
            'ip_semester_1.min' => 'IP Semester 1 minimal 0.00',
            'ip_semester_1.max' => 'IP Semester 1 maksimal 4.00',
            'ip_semester_2.required' => 'IP Semester 2 wajib diisi',
        ];
    }
}