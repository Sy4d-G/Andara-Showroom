<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSalesTransactionRequest extends FormRequest
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
            'motor_id' => [
                'required',
                'integer',
                Rule::exists('motors', 'id')->where(function ($query) {
                    $query->where('status', '!=', 'terjual');
                }),
            ],
            'nama_pembeli' => ['required', 'string', 'max:255'],
            'no_telepon_pembeli' => ['required', 'string', 'max:50'],
            'alamat_pembeli' => ['nullable', 'string'],
            'tanggal_transaksi' => ['required', 'date'],
            'harga_jual' => ['required', 'integer', 'min:0'],
            'harga_beli' => ['nullable', 'integer', 'min:0'],
            'metode_pembayaran' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'motor_id.exists' => 'Motor yang dipilih tidak ditemukan atau sudah berstatus terjual.',
        ];
    }
}
