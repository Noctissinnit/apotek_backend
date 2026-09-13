# Apotek Backend — REST API

Backend apotek berbasis **Laravel 12 + MySQL**, sudah termasuk contoh master data obat
dan konfigurasi deploy ke **Vercel**.

Fitur:

- CRUD **obat** lengkap dengan pencarian, filter, sorting, dan paginasi
- Master **kategori** dan **supplier**
- **Transaksi penjualan** dengan pengurangan stok yang aman dari oversell (row lock + DB transaction)
- Penyesuaian stok (barang masuk / keluar / stok opname)
- Peringatan **stok menipis** dan **obat mendekati kadaluarsa**
- Ringkasan **statistik** untuk dashboard
- Proteksi endpoint tulis lewat header `X-API-KEY` + rate limit per menit
- Respons JSON konsisten, termasuk untuk error dan validasi

---

## 1. Menjalankan di lokal

Prasyarat: PHP 8.2+, Composer, MySQL 8 (XAMPP / Laragon / MySQL Server).

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database, lalu sesuaikan `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apotek_db
DB_USERNAME=root
DB_PASSWORD=
```

```sql
CREATE DATABASE apotek_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Jalankan migrasi + data contoh, lalu nyalakan server:

```bash
php artisan migrate --seed
php artisan serve
```

API siap di `http://localhost:8000/api`.

### Alternatif: impor file SQL langsung

Kalau lebih suka lewat phpMyAdmin/Workbench, tersedia dump siap pakai di
[`database/sql/apotek_db.sql`](database/sql/apotek_db.sql) — berisi `CREATE DATABASE`,
struktur tabel, dan seluruh data contoh (43 obat, 11 kategori, 5 supplier, 8 transaksi).

```bash
mysql -u root -p < database/sql/apotek_db.sql
```

> File ini digenerate dari seeder yang sama, jadi isinya identik dengan hasil
> `php artisan migrate --seed`.

---

## 2. Struktur data

| Tabel | Isi |
|---|---|
| `kategori` | Golongan produk apotek (analgesik, antibiotik, alkes, dst.) |
| `supplier` | Distributor/PBF beserta kontaknya |
| `obat` | Master obat: kode, harga beli/jual, stok, kadaluarsa, golongan obat |
| `penjualan` | Header transaksi: kode, total, bayar, kembalian, metode bayar |
| `detail_penjualan` | Item per transaksi (nama & harga di-*snapshot* saat transaksi) |

Catatan desain:

- `obat` memakai **soft delete** supaya riwayat penjualan lama tidak ikut rusak saat obat dihapus.
- `detail_penjualan` menyimpan salinan `nama_obat` dan `harga_satuan`, sehingga struk lama
  tetap benar walau master obat diedit belakangan.
- `golongan` memakai ENUM sesuai penggolongan obat: `bebas`, `bebas_terbatas`, `keras`,
  `narkotika`, `psikotropika`, `herbal`.

---

## 3. Daftar endpoint

Base URL: `/api`

### Umum

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api` | Info API dan daftar endpoint |
| GET | `/api/health` | Cek status aplikasi + koneksi database |
| GET | `/api/statistik` | Ringkasan dashboard (nilai stok, omzet, obat terlaris) |
| GET | `/api/obat-stok-menipis` | Obat yang stoknya sudah <= stok minimum |

### Obat

| Method | Endpoint | Keterangan |
|---|---|---|
| GET | `/api/obat` | Daftar obat (paginasi) |
| GET | `/api/obat/{id}` | Detail satu obat |
| POST | `/api/obat` | Tambah obat |
| PUT/PATCH | `/api/obat/{id}` | Ubah obat |
| PATCH | `/api/obat/{id}/stok` | Tambah / kurangi / set stok |
| DELETE | `/api/obat/{id}` | Hapus obat (soft delete) |

Query parameter `GET /api/obat`:

| Parameter | Contoh | Keterangan |
|---|---|---|
| `search` | `paracetamol` | Cari di nama, kode, kandungan, produsen |
| `kategori_id` | `2` | Filter kategori |
| `supplier_id` | `1` | Filter supplier |
| `golongan` | `keras` | Filter golongan obat |
| `aktif` | `1` | Hanya obat aktif |
| `stok_menipis` | `1` | Hanya obat yang stoknya menipis |
| `akan_kadaluarsa` | `90` | Kadaluarsa dalam N hari ke depan |
| `sort_by` | `harga_jual` | `nama`, `kode_obat`, `harga_beli`, `harga_jual`, `stok`, `tanggal_kadaluarsa`, `created_at` |
| `sort_dir` | `desc` | `asc` (default) atau `desc` |
| `per_page` | `25` | Maksimal 100, default 15 |
| `page` | `2` | Halaman |

### Kategori, Supplier, Penjualan

| Method | Endpoint |
|---|---|
| GET / POST | `/api/kategori`, `/api/supplier`, `/api/penjualan` |
| GET | `/api/kategori/{id}`, `/api/supplier/{id}`, `/api/penjualan/{id}` |
| PUT/PATCH | `/api/kategori/{id}`, `/api/supplier/{id}` |
| DELETE | `/api/kategori/{id}`, `/api/supplier/{id}`, `/api/penjualan/{id}` |

`DELETE /api/penjualan/{id}` membatalkan transaksi **dan mengembalikan stok** obatnya.

Filter `GET /api/penjualan`: `search`, `tanggal_dari`, `tanggal_sampai`, `metode_bayar`, `per_page`.

---

## 4. Contoh pemakaian

### Cari obat

```bash
curl "http://localhost:8000/api/obat?search=paracetamol&per_page=5"
```

### Tambah obat

```bash
curl -X POST http://localhost:8000/api/obat \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: kunci-anda" \
  -d '{
    "kode_obat": "OBT-1000",
    "nama": "Vitamin B Kompleks",
    "kategori_id": 3,
    "supplier_id": 1,
    "golongan": "bebas",
    "bentuk_sediaan": "Tablet",
    "satuan": "strip",
    "harga_beli": 4000,
    "harga_jual": 6500,
    "stok": 50,
    "stok_minimum": 10,
    "tanggal_kadaluarsa": "2028-01-31"
  }'
```

### Barang masuk (tambah stok)

```bash
curl -X PATCH http://localhost:8000/api/obat/1/stok \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: kunci-anda" \
  -d '{"tipe": "tambah", "jumlah": 100}'
```

`tipe` yang tersedia: `tambah` (barang masuk), `kurang` (keluar/rusak), `set` (stok opname).

### Transaksi penjualan

```bash
curl -X POST http://localhost:8000/api/penjualan \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: kunci-anda" \
  -d '{
    "nama_pelanggan": "Ibu Dewi",
    "metode_bayar": "tunai",
    "bayar": 100000,
    "items": [
      {"obat_id": 1, "jumlah": 2},
      {"obat_id": 7, "jumlah": 1}
    ]
  }'
```

Harga **selalu diambil dari master obat**, bukan dari client. Item dengan `obat_id`
yang sama otomatis digabung, stok dikunci selama transaksi, dan seluruh perubahan
di-*rollback* kalau ada satu item yang stoknya kurang atau uang bayar tidak cukup.

### Bentuk respons

Sukses (list):

```json
{
  "data": [ { "id": 1, "kode_obat": "OBT-0001", "nama": "Paracetamol 500 mg", "...": "..." } ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 15, "total": 43 },
  "success": true
}
```

Gagal validasi (HTTP 422):

```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid.",
  "errors": {
    "harga_jual": ["Harga jual tidak boleh lebih kecil dari harga beli."]
  }
}
```

---

## 5. Keamanan

Endpoint **baca** (GET) terbuka. Endpoint **tulis** (POST/PUT/PATCH/DELETE) dilindungi
middleware `apikey`:

- Kalau env `API_KEY` **kosong** → middleware tidak aktif (praktis saat development).
- Kalau `API_KEY` **diisi** → setiap request tulis wajib mengirim
  `X-API-KEY: <kunci>` atau `Authorization: Bearer <kunci>`, kalau tidak dijawab `401`.

Rate limit default 60 request/menit per IP (atau per API key), diatur lewat `API_RATE_LIMIT`.

> Untuk aplikasi produksi dengan banyak user/petugas apotek, ganti lapisan ini
> dengan Laravel Sanctum (`php artisan install:api`) agar tiap petugas punya token sendiri.

---

## 6. Deploy ke Vercel

### 6.1 Siapkan database MySQL

Vercel tidak menyediakan MySQL, jadi databasenya harus di layanan lain, misalnya:

| Layanan | Catatan |
|---|---|
| **Aiven for MySQL** | Ada free tier, wajib TLS + file CA |
| **TiDB Cloud (Serverless)** | Kompatibel MySQL, free tier besar, wajib TLS |
| **Railway / Clever Cloud** | Setup paling gampang |
| **PlanetScale** | Kompatibel MySQL, tidak mendukung foreign key — migrasi perlu disesuaikan |

Setelah database jadi, jalankan migrasi + seeder dari komputer lokal ke database cloud
(arahkan `.env` ke kredensial cloud), atau impor `database/sql/apotek_db.sql`:

```bash
php artisan migrate --seed --force
```

### 6.2 File yang sudah disiapkan

| File | Fungsi |
|---|---|
| [`vercel.json`](vercel.json) | Runtime `vercel-php@0.9.0` (PHP 8.5), semua request diarahkan ke satu function |
| [`api/index.php`](api/index.php) | Entry point serverless; memindahkan `storage/` & `bootstrap/cache` ke `/tmp` |
| [`.vercelignore`](.vercelignore) | Menahan `.env`, `vendor`, database lokal ikut terkirim |

Filesystem Vercel **read-only** kecuali `/tmp`, karena itu `api/index.php` mengeset
`LARAVEL_STORAGE_PATH` dan variabel `APP_*_CACHE` ke `/tmp` sebelum Laravel di-boot.

`composer install` dijalankan otomatis oleh runtime saat build, jadi folder `vendor/`
tidak perlu ikut di-commit. Kalau butuh PHP versi lain, ganti angka runtime di
`vercel.json` (daftar versi: <https://github.com/vercel-community/php>).

### 6.3 Deploy

```bash
npm i -g vercel
vercel login
vercel            # deploy preview
vercel --prod     # deploy production
```

Atau: push ke GitHub → **Import Project** di dashboard Vercel (framework preset: *Other*).

### 6.4 Environment variables di Vercel

Set di **Project → Settings → Environment Variables**:

```env
APP_NAME=Apotek API
APP_ENV=production
APP_KEY=base64:xxxxx        # ambil dari output `php artisan key:generate --show`
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://nama-project.vercel.app

DB_CONNECTION=mysql
DB_HOST=host-mysql-anda
DB_PORT=3306
DB_DATABASE=apotek_db
DB_USERNAME=user
DB_PASSWORD=password

API_KEY=kunci-rahasia-anda
API_RATE_LIMIT=60

LOG_CHANNEL=stderr          # log masuk ke Vercel Function Logs
SESSION_DRIVER=array
CACHE_STORE=array
QUEUE_CONNECTION=sync
```

**Wajib:** `APP_KEY` harus diisi, dan `SESSION_DRIVER`/`CACHE_STORE` harus `array`
(driver `database`/`file` butuh storage persisten yang tidak ada di serverless).

Kalau provider MySQL memaksa TLS:

- Punya file CA → simpan di `certs/ca.pem`, set `MYSQL_ATTR_SSL_CA=/var/task/certs/ca.pem`
- Tidak punya file CA → set `DB_SSL_VERIFY=false` (koneksi tetap terenkripsi, sertifikat tidak diverifikasi)

### 6.5 Verifikasi setelah deploy

```bash
curl https://nama-project.vercel.app/api/health
curl https://nama-project.vercel.app/api/obat?per_page=3
```

`/api/health` akan menjawab `"database": "connected"` kalau koneksi MySQL berhasil.

### 6.6 Troubleshooting

**`No Output Directory named "dist" found after the Build completed`**

Skeleton Laravel membawa `package.json` + `vite.config.js`, sehingga Vercel mengira ini
proyek Vite, menjalankan `vite build`, lalu mencari folder `dist`. Proyek ini API-only,
jadi `vercel.json` sudah menimpa pengaturan tersebut:

```json
"framework": null,
"installCommand": "",
"buildCommand": "echo 'API only: tidak ada build frontend'",
"outputDirectory": "public"
```

dan `.vercelignore` menahan `package.json` / `vite.config.js` agar tidak ikut terkirim.
Nilai di `vercel.json` selalu menang atas Project Settings di dashboard, jadi tidak perlu
mengubah apa pun di sana. Cukup commit lalu **Redeploy**.

**`date_default_timezone_set(): Timezone ID '' is invalid`** (atau error lain soal nilai `[]`)

Ada Environment Variable di Vercel yang **dibuat tapi nilainya kosong**. Fallback bawaan
`env('X', 'default')` hanya berlaku kalau variabelnya tidak ada sama sekali, jadi string
kosong ikut terbaca. Config proyek ini sudah memakai `env('X') ?: 'default'` untuk
variabel yang fatal kalau kosong:

| Variabel | Fallback kalau kosong |
|---|---|
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `CACHE_STORE` | `array` |
| `SESSION_DRIVER` | `array` |
| `DB_CONNECTION` | `mysql` |
| `API_RATE_LIMIT` | `60` |
| `DB_TIMEOUT` | `10` |

Tetap rapikan dashboard: hapus variabel yang tidak dipakai, dan pastikan `APP_KEY`
serta `DB_HOST` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` benar-benar terisi.

---

## 7. Testing

```bash
php artisan test
```

14 test mencakup CRUD obat, validasi harga, penyesuaian stok, transaksi penjualan
(termasuk penolakan saat stok/uang kurang beserta rollback-nya), pembatalan transaksi,
proteksi API key, dan format error JSON.

Untuk mencoba API tanpa MySQL, tersedia `.env.testing` berbasis SQLite:

```bash
php artisan --env=testing migrate:fresh --seed
php artisan serve --env=testing
```

---

## 8. Struktur proyek

```
api/index.php                     Entry point Vercel (serverless)
app/Http/Controllers/Api/         ObatController, KategoriController, SupplierController,
                                  PenjualanController, StatistikController
app/Http/Requests/                Validasi (ObatRequest, PenjualanRequest, StokRequest, ...)
app/Http/Resources/               Formatter respons JSON
app/Http/Middleware/ApiKey.php    Proteksi endpoint tulis
app/Models/                       Obat, Kategori, Supplier, Penjualan, DetailPenjualan
config/apotek.php                 API key & rate limit
database/migrations/              Skema 5 tabel apotek
database/seeders/                 Data contoh (kategori, supplier, 43 obat, 8 transaksi)
database/sql/apotek_db.sql        Dump MySQL siap impor
routes/api.php                    Definisi endpoint
tests/Feature/ApotekApiTest.php   Test API
vercel.json                       Konfigurasi deploy
```
