<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ObatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $obatId = $this->route('obat')?->id;
        $wajib = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'kode_obat' => [
                $wajib, 'string', 'max:30',
                Rule::unique('obat', 'kode_obat')->ignore($obatId)->whereNull('deleted_at'),
            ],
            'nama' => [$wajib, 'string', 'max:150'],
            'kategori_id' => [$wajib, 'integer', 'exists:kategori,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:supplier,id'],
            'golongan' => [
                'sometimes',
                Rule::in(['bebas', 'bebas_terbatas', 'keras', 'narkotika', 'psikotropika', 'herbal']),
            ],
            'bentuk_sediaan' => ['nullable', 'string', 'max:50'],
            'satuan' => ['sometimes', 'string', 'max:20'],
            'kandungan' => ['nullable', 'string', 'max:191'],
            'produsen' => ['nullable', 'string', 'max:150'],
            'harga_beli' => [$wajib, 'numeric', 'min:0', 'max:99999999.99'],
            'harga_jual' => [$wajib, 'numeric', 'min:0', 'max:99999999.99', 'gte:harga_beli'],
            'stok' => ['sometimes', 'integer', 'min:0'],
            'stok_minimum' => ['sometimes', 'integer', 'min:0'],
            'tanggal_kadaluarsa' => ['nullable', 'date'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_obat.unique' => 'Kode obat sudah dipakai obat lain.',
            'kategori_id.exists' => 'Kategori tidak ditemukan.',
            'supplier_id.exists' => 'Supplier tidak ditemukan.',
            'harga_jual.gte' => 'Harga jual tidak boleh lebih kecil dari harga beli.',
        ];
    }
}
