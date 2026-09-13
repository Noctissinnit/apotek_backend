<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenjualanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => ['nullable', 'string', 'max:150'],
            'metode_bayar' => ['sometimes', Rule::in(['tunai', 'debit', 'kredit', 'qris', 'transfer'])],
            'bayar' => ['required', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'integer', 'exists:obat,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Transaksi harus berisi minimal satu item obat.',
            'items.*.obat_id.exists' => 'Obat pada salah satu item tidak ditemukan.',
        ];
    }
}
