<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ObatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_obat' => $this->kode_obat,
            'nama' => $this->nama,
            'golongan' => $this->golongan,
            'bentuk_sediaan' => $this->bentuk_sediaan,
            'satuan' => $this->satuan,
            'kandungan' => $this->kandungan,
            'produsen' => $this->produsen,
            'harga_beli' => (float) $this->harga_beli,
            'harga_jual' => (float) $this->harga_jual,
            'stok' => $this->stok,
            'stok_minimum' => $this->stok_minimum,
            'stok_menipis' => $this->stok <= $this->stok_minimum,
            'tanggal_kadaluarsa' => $this->tanggal_kadaluarsa?->toDateString(),
            'deskripsi' => $this->deskripsi,
            'aktif' => $this->aktif,
            'kategori' => new KategoriResource($this->whenLoaded('kategori')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
