<?php
// app/Http/Requests/PrediksiArrayRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrediksiArrayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File Excel wajib diupload',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
            'file.max' => 'Ukuran file maksimal 10MB',
        ];
    }
}