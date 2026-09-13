<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenjualanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode_transaksi' => $this->kode_transaksi,
            'tanggal' => $this->tanggal?->toIso8601String(),
            'nama_pelanggan' => $this->nama_pelanggan,
            'total' => (float) $this->total,
            'bayar' => (float) $this->bayar,
            'kembalian' => (float) $this->kembalian,
            'metode_bayar' => $this->metode_bayar,
            'catatan' => $this->catatan,
            'jumlah_item' => $this->whenCounted('detail'),
            'detail' => DetailPenjualanResource::collection($this->whenLoaded('detail')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
