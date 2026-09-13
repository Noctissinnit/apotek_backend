<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Obat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'obat';

    protected $fillable = [
        'kode_obat', 'nama', 'kategori_id', 'supplier_id', 'golongan',
        'bentuk_sediaan', 'satuan', 'kandungan', 'produsen',
        'harga_beli', 'harga_jual', 'stok', 'stok_minimum',
        'tanggal_kadaluarsa', 'deskripsi', 'aktif',
    ];

    protected function casts(): array
    {
        return [
            'harga_beli' => 'decimal:2',
            'harga_jual' => 'decimal:2',
            'stok' => 'integer',
            'stok_minimum' => 'integer',
            'tanggal_kadaluarsa' => 'date',
            'aktif' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class);
    }

    /** Cari berdasarkan nama, kode, kandungan, atau produsen. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('nama', 'like', $like)
                ->orWhere('kode_obat', 'like', $like)
                ->orWhere('kandungan', 'like', $like)
                ->orWhere('produsen', 'like', $like);
        });
    }

    /** Stok sudah menyentuh atau di bawah batas minimum. */
    public function scopeStokMenipis(Builder $query): Builder
    {
        return $query->whereColumn('stok', '<=', 'stok_minimum');
    }

    /** Kadaluarsa dalam N hari ke depan (termasuk yang sudah lewat). */
    public function scopeAkanKadaluarsa(Builder $query, int $hari = 90): Builder
    {
        return $query->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays($hari));
    }

    public function getStokMenipisAttribute(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }
}
