<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';

    protected $fillable = [
        'user_id', 'kode_transaksi', 'tanggal', 'nama_pelanggan',
        'total', 'bayar', 'kembalian', 'metode_bayar', 'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
            'total' => 'decimal:2',
            'bayar' => 'decimal:2',
            'kembalian' => 'decimal:2',
        ];
    }

    /** Kasir yang mencatat transaksi. */
    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class);
    }

    /** Kode transaksi berurutan per hari: TRX-20260909-0001 */
    public static function generateKode(): string
    {
        $prefix = 'TRX-'.now()->format('Ymd').'-';
        $terakhir = static::where('kode_transaksi', 'like', $prefix.'%')
            ->lockForUpdate()
            ->max('kode_transaksi');

        $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $prefix.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
    }
}
