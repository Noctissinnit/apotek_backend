@extends('layouts.app')

@section('title', 'Kasir')

@section('content')
@include('partials.page-header', [
    'judul' => 'Kasir',
    'ikon' => 'cart-plus',
    'sub' => 'Kasir: '.auth()->user()->name,
])

<form method="POST" action="{{ route('penjualan.store') }}" id="form-kasir" novalidate>
    @csrf

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold" for="cari-obat">Cari obat</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" id="cari-obat" class="form-control form-control-lg" list="daftar-obat" autocomplete="off"
                               placeholder="Ketik nama atau kode obat, lalu Enter" autofocus>
                        <button type="button" class="btn btn-apotek" id="btn-tambah"><i class="bi bi-plus-lg"></i> Tambah</button>
                    </div>
                    <datalist id="daftar-obat">
                        @foreach($obat as $o)
                            <option value="{{ $o['kode'] }} · {{ $o['nama'] }}">Stok {{ $o['stok'] }} · Rp {{ number_format($o['harga'], 0, ',', '.') }}</option>
                        @endforeach
                    </datalist>
                    <div class="form-text" id="info-cari">{{ count($obat) }} obat tersedia untuk dijual.</div>
                </div>
            </div>

            <div class="card">
                @if($errors->has('items') || $errors->has('items.*'))
                    <div class="alert alert-danger m-3 mb-0">
                        @foreach($errors->get('items') as $pesan) <div>{{ $pesan }}</div> @endforeach
                        @foreach($errors->get('items.*') as $daftar) @foreach($daftar as $pesan) <div>{{ $pesan }}</div> @endforeach @endforeach
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th>Obat</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center" style="width: 9rem;">Jumlah</th>
                            <th class="text-end">Subtotal</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="item-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card kasir-ringkasan">
                <div class="card-body">
                    <div class="text-body-secondary small">Total belanja</div>
                    <div class="kasir-total mb-3" id="total">Rp 0</div>

                    <div class="mb-3">
                        <label class="form-label" for="metode_bayar">Metode bayar</label>
                        <select id="metode_bayar" name="metode_bayar" class="form-select">
                            @foreach($metodeBayar as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('metode_bayar', 'tunai') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="bayar">Uang dibayar</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text">Rp</span>
                            <input type="number" id="bayar" name="bayar" min="0" step="1" required inputmode="numeric"
                                   value="{{ old('bayar') }}" class="form-control form-control-lg @error('bayar') is-invalid @enderror">
                            @error('bayar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mb-3" id="uang-cepat">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bayar="pas">Uang pas</button>
                        @foreach([10000, 20000, 50000, 100000] as $nominal)
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bayar="{{ $nominal }}">{{ number_format($nominal / 1000) }}rb</button>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 rounded bg-body-tertiary mb-3">
                        <span class="text-body-secondary" id="label-kembalian">Kembalian</span>
                        <span class="fs-5 fw-bold" id="kembalian">Rp 0</span>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="nama_pelanggan">Nama pelanggan <span class="text-body-secondary small">(opsional)</span></label>
                        <input type="text" id="nama_pelanggan" name="nama_pelanggan" maxlength="150" value="{{ old('nama_pelanggan') }}"
                               class="form-control @error('nama_pelanggan') is-invalid @enderror">
                        @error('nama_pelanggan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="catatan">Catatan <span class="text-body-secondary small">(opsional)</span></label>
                        <input type="text" id="catatan" name="catatan" maxlength="255" value="{{ old('catatan') }}"
                               class="form-control @error('catatan') is-invalid @enderror" placeholder="Mis. resep dr. …">
                        @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-apotek btn-lg w-100" id="btn-simpan" disabled>
                        <i class="bi bi-check2-circle me-1"></i> Simpan Transaksi
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const OBAT = @json($obat);
    const ITEM_LAMA = @json(old('items', []));

    const rupiah = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');
    const label = (o) => `${o.kode} · ${o.nama}`;

    const obatById = new Map(OBAT.map((o) => [String(o.id), o]));
    const keranjang = new Map(); // id obat -> jumlah (urutan tambah dipertahankan)

    const el = {
        cari: document.getElementById('cari-obat'),
        info: document.getElementById('info-cari'),
        body: document.getElementById('item-body'),
        total: document.getElementById('total'),
        bayar: document.getElementById('bayar'),
        kembalian: document.getElementById('kembalian'),
        labelKembalian: document.getElementById('label-kembalian'),
        simpan: document.getElementById('btn-simpan'),
    };

    function info(pesan, bahaya = false) {
        el.info.textContent = pesan;
        el.info.classList.toggle('text-danger', bahaya);
    }

    /** Cocokkan teks ketikan: label datalist, kode persis, atau satu-satunya nama yang mengandung teks itu. */
    function cariObat(teks) {
        const q = teks.trim().toLowerCase();
        if (!q) return null;

        const persis = OBAT.find((o) => label(o).toLowerCase() === q || o.kode.toLowerCase() === q);
        if (persis) return persis;

        const cocok = OBAT.filter((o) => o.nama.toLowerCase().includes(q) || o.kode.toLowerCase().includes(q));
        return cocok.length === 1 ? cocok[0] : null;
    }

    function tambah(o, jumlah = 1) {
        const id = String(o.id);
        const sekarang = keranjang.get(id) || 0;
        const baru = Math.min(o.stok, sekarang + jumlah);

        if (sekarang + jumlah > o.stok) {
            info(`Stok ${o.nama} hanya ${o.stok} ${o.satuan}.`, true);
        } else {
            info(`${o.nama} ditambahkan.`);
        }

        keranjang.set(id, baru);
        render();
    }

    function totalBelanja() {
        let total = 0;
        for (const [id, jumlah] of keranjang) total += obatById.get(id).harga * jumlah;
        return total;
    }

    function hitung() {
        const total = totalBelanja();
        const bayar = parseFloat(el.bayar.value) || 0;
        const selisih = bayar - total;

        el.total.textContent = rupiah(total);
        el.kembalian.textContent = rupiah(Math.abs(selisih));
        el.labelKembalian.textContent = selisih < 0 ? 'Kurang' : 'Kembalian';
        el.kembalian.classList.toggle('text-danger', selisih < 0);
        el.simpan.disabled = keranjang.size === 0 || selisih < 0 || el.bayar.value === '';
    }

    function render() {
        el.body.replaceChildren();

        if (keranjang.size === 0) {
            const tr = el.body.insertRow();
            const td = tr.insertCell();
            td.colSpan = 5;
            td.className = 'text-center text-body-secondary py-5';
            td.textContent = 'Belum ada obat. Cari obat di atas untuk mulai transaksi.';
            hitung();
            return;
        }

        let i = 0;
        for (const [id, jumlah] of keranjang) {
            const o = obatById.get(id);
            const tr = el.body.insertRow();

            // Nama & kode ditulis lewat textContent supaya aman dari HTML di data obat.
            const tdNama = tr.insertCell();
            const nama = document.createElement('div');
            nama.className = 'fw-semibold';
            nama.textContent = o.nama;
            const kode = document.createElement('div');
            kode.className = 'small text-body-secondary';
            kode.textContent = `${o.kode} · stok ${o.stok} ${o.satuan}`;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `items[${i}][obat_id]`;
            hidden.value = o.id;
            tdNama.append(nama, kode, hidden);

            const tdHarga = tr.insertCell();
            tdHarga.className = 'text-end text-nowrap';
            tdHarga.textContent = rupiah(o.harga);

            const tdJumlah = tr.insertCell();
            const qty = document.createElement('input');
            qty.type = 'number';
            qty.min = 1;
            qty.max = o.stok;
            qty.value = jumlah;
            qty.name = `items[${i}][jumlah]`;
            qty.className = 'form-control form-control-sm text-center';
            qty.setAttribute('aria-label', `Jumlah ${o.nama}`);
            tdJumlah.append(qty);

            const tdSub = tr.insertCell();
            tdSub.className = 'text-end fw-semibold text-nowrap';
            tdSub.textContent = rupiah(o.harga * jumlah);

            qty.addEventListener('input', () => {
                let n = parseInt(qty.value, 10);
                if (Number.isNaN(n) || n < 1) return; // biarkan kasir selesai mengetik
                if (n > o.stok) {
                    n = o.stok;
                    qty.value = n;
                    info(`Stok ${o.nama} hanya ${o.stok} ${o.satuan}.`, true);
                }
                keranjang.set(id, n);
                tdSub.textContent = rupiah(o.harga * n);
                hitung();
            });
            qty.addEventListener('blur', () => {
                if (!(parseInt(qty.value, 10) >= 1)) qty.value = keranjang.get(id);
            });

            const tdHapus = tr.insertCell();
            tdHapus.className = 'text-end';
            const hapus = document.createElement('button');
            hapus.type = 'button';
            hapus.className = 'btn btn-sm btn-outline-danger';
            hapus.title = `Hapus ${o.nama}`;
            hapus.innerHTML = '<i class="bi bi-x-lg"></i>';
            hapus.addEventListener('click', () => {
                keranjang.delete(id);
                render();
                el.cari.focus();
            });
            tdHapus.append(hapus);

            i++;
        }

        hitung();
    }

    function tambahDariKetikan() {
        const o = cariObat(el.cari.value);
        if (!o) {
            if (el.cari.value.trim()) info('Obat tidak ditemukan atau lebih dari satu yang cocok. Pilih dari daftar.', true);
            return;
        }
        tambah(o);
        el.cari.value = '';
        el.cari.focus();
    }

    el.cari.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault(); // jangan submit form
            tambahDariKetikan();
        }
    });
    // Memilih dari daftar saran langsung menambahkan obat.
    el.cari.addEventListener('input', () => {
        const q = el.cari.value.trim().toLowerCase();
        if (OBAT.some((o) => label(o).toLowerCase() === q)) tambahDariKetikan();
    });
    document.getElementById('btn-tambah').addEventListener('click', tambahDariKetikan);

    el.bayar.addEventListener('input', hitung);
    document.querySelectorAll('#uang-cepat [data-bayar]').forEach((btn) => {
        btn.addEventListener('click', () => {
            el.bayar.value = btn.dataset.bayar === 'pas' ? Math.round(totalBelanja()) : btn.dataset.bayar;
            hitung();
        });
    });

    // Cegah transaksi terkirim dua kali karena tombol diklik ganda.
    document.getElementById('form-kasir').addEventListener('submit', () => {
        el.simpan.disabled = true;
        el.simpan.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan…';
    });

    // Isi ulang keranjang setelah validasi gagal (old input dari server).
    for (const item of Object.values(ITEM_LAMA)) {
        const o = obatById.get(String(item.obat_id));
        const jumlah = parseInt(item.jumlah, 10);
        if (o && jumlah > 0) keranjang.set(String(o.id), Math.min(o.stok, jumlah));
    }

    render();
})();
</script>
@endpush
