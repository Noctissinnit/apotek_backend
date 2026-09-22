@csrf

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Informasi obat</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="kode_obat">Kode obat <span class="text-danger">*</span></label>
                    <input type="text" id="kode_obat" name="kode_obat" maxlength="30" required
                           value="{{ old('kode_obat', $obat->kode_obat) }}"
                           class="form-control @error('kode_obat') is-invalid @enderror" placeholder="OBT-0001">
                    @error('kode_obat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="nama">Nama obat <span class="text-danger">*</span></label>
                    <input type="text" id="nama" name="nama" maxlength="150" required
                           value="{{ old('nama', $obat->nama) }}"
                           class="form-control @error('nama') is-invalid @enderror" placeholder="Paracetamol 500 mg">
                    @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="kategori_id">Kategori <span class="text-danger">*</span></label>
                    <select id="kategori_id" name="kategori_id" required class="form-select @error('kategori_id') is-invalid @enderror">
                        <option value="">Pilih kategori</option>
                        @foreach($kategori as $k)
                            <option value="{{ $k->id }}" @selected(old('kategori_id', $obat->kategori_id) == $k->id)>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="golongan">Golongan <span class="text-danger">*</span></label>
                    <select id="golongan" name="golongan" required class="form-select @error('golongan') is-invalid @enderror">
                        @foreach($golongan as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('golongan', $obat->golongan) === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('golongan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="bentuk_sediaan">Bentuk sediaan</label>
                    <input type="text" id="bentuk_sediaan" name="bentuk_sediaan" maxlength="50" list="daftar-sediaan"
                           value="{{ old('bentuk_sediaan', $obat->bentuk_sediaan) }}"
                           class="form-control @error('bentuk_sediaan') is-invalid @enderror" placeholder="Tablet, Sirup, Krim, …">
                    <datalist id="daftar-sediaan">
                        @foreach(['Tablet', 'Kaplet', 'Kapsul', 'Sirup', 'Suspensi', 'Tetes', 'Krim', 'Salep', 'Larutan', 'Serbuk', 'Injeksi'] as $s)
                            <option value="{{ $s }}"></option>
                        @endforeach
                    </datalist>
                    @error('bentuk_sediaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="satuan">Satuan jual <span class="text-danger">*</span></label>
                    <input type="text" id="satuan" name="satuan" maxlength="20" required list="daftar-satuan"
                           value="{{ old('satuan', $obat->satuan) }}"
                           class="form-control @error('satuan') is-invalid @enderror">
                    <datalist id="daftar-satuan">
                        @foreach(['strip', 'botol', 'tube', 'box', 'sachet', 'pcs', 'tablet', 'kapsul'] as $s)
                            <option value="{{ $s }}"></option>
                        @endforeach
                    </datalist>
                    @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="kandungan">Kandungan</label>
                    <input type="text" id="kandungan" name="kandungan" maxlength="191"
                           value="{{ old('kandungan', $obat->kandungan) }}"
                           class="form-control @error('kandungan') is-invalid @enderror">
                    @error('kandungan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="produsen">Produsen</label>
                    <input type="text" id="produsen" name="produsen" maxlength="150"
                           value="{{ old('produsen', $obat->produsen) }}"
                           class="form-control @error('produsen') is-invalid @enderror">
                    @error('produsen') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="supplier_id">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                        <option value="">Tidak ada</option>
                        @foreach($supplier as $s)
                            <option value="{{ $s->id }}" @selected(old('supplier_id', $obat->supplier_id) == $s->id)>{{ $s->nama }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="tanggal_kadaluarsa">Tanggal kadaluarsa</label>
                    <input type="date" id="tanggal_kadaluarsa" name="tanggal_kadaluarsa"
                           value="{{ old('tanggal_kadaluarsa', $obat->tanggal_kadaluarsa?->format('Y-m-d')) }}"
                           class="form-control @error('tanggal_kadaluarsa') is-invalid @enderror">
                    @error('tanggal_kadaluarsa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label" for="deskripsi">Deskripsi / aturan pakai</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="2000"
                              class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $obat->deskripsi) }}</textarea>
                    @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header">Harga</div>
            <div class="card-body row g-3">
                <div class="col-12">
                    <label class="form-label" for="harga_beli">Harga beli <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="harga_beli" name="harga_beli" min="0" step="1" required
                               value="{{ old('harga_beli', $obat->harga_beli !== null ? (float) $obat->harga_beli : '') }}"
                               class="form-control @error('harga_beli') is-invalid @enderror">
                        @error('harga_beli') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label" for="harga_jual">Harga jual <span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="harga_jual" name="harga_jual" min="0" step="1" required
                               value="{{ old('harga_jual', $obat->harga_jual !== null ? (float) $obat->harga_jual : '') }}"
                               class="form-control @error('harga_jual') is-invalid @enderror">
                        @error('harga_jual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">Stok</div>
            <div class="card-body row g-3">
                <div class="col-6">
                    @if($obat->exists)
                        {{-- Sengaja tanpa atribut name: stok hanya berubah lewat Atur Stok / penjualan. --}}
                        <label class="form-label" for="stok-sekarang">Stok saat ini</label>
                        <input type="text" id="stok-sekarang" class="form-control" value="{{ $obat->stok }} {{ $obat->satuan }}" disabled>
                    @else
                        <label class="form-label" for="stok">Stok awal</label>
                        <input type="number" id="stok" name="stok" min="0" step="1"
                               value="{{ old('stok', $obat->stok) }}"
                               class="form-control @error('stok') is-invalid @enderror">
                        @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>
                <div class="col-6">
                    <label class="form-label" for="stok_minimum">Stok minimum</label>
                    <input type="number" id="stok_minimum" name="stok_minimum" min="0" step="1"
                           value="{{ old('stok_minimum', $obat->stok_minimum) }}"
                           class="form-control @error('stok_minimum') is-invalid @enderror">
                    @error('stok_minimum') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                @if($obat->exists)
                    <div class="col-12 form-text mt-1">Untuk barang masuk/keluar gunakan menu <strong>Atur Stok</strong>.</div>
                @endif
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="aktif" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="aktif" name="aktif" value="1"
                               @checked(old('aktif', $obat->aktif))>
                        <label class="form-check-label" for="aktif">Aktif dijual di kasir</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-apotek"><i class="bi bi-save me-1"></i> Simpan</button>
            <a href="{{ $obat->exists ? route('obat.show', $obat) : route('obat.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </div>
</div>
