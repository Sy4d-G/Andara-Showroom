<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMotorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_motor' => ['required', 'string', 'max:255'],
            'merk' => ['required', 'string', 'max:100'],
            'tipe_motor' => ['required', 'string', 'max:100'],
            'tahun' => ['required', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'harga_beli' => ['required', 'integer', 'min:0'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'kondisi' => ['required', 'string'],
            'jarak_tempuh' => ['required', 'integer', 'min:0'],
            'surat_surat' => ['required', 'string', 'max:255'],
            'pajak' => ['required', 'string', 'max:255'],
            'garansi' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:tersedia,terjual,booking'],
            'deskripsi' => ['nullable', 'string'],
            'foto_utama' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
