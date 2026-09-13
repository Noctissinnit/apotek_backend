<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailPenjualanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'obat_id' => $this->obat_id,
            'nama_obat' => $this->nama_obat,
            'jumlah' => $this->jumlah,
            'harga_satuan' => (float) $this->harga_satuan,
            'subtotal' => (float) $this->subtotal,
            'obat' => new ObatResource($this->whenLoaded('obat')),
        ];
    }
}
