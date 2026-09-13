<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // tambah = barang masuk, kurang = keluar/rusak, set = stok opname
            'tipe' => ['required', Rule::in(['tambah', 'kurang', 'set'])],
            'jumlah' => ['required', 'integer', 'min:0'],
        ];
    }
}
