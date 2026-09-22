# Aplikasi Apotek (Laravel)

Aplikasi web apotek berbasis **Laravel 12 + MySQL** dengan tampilan Blade + Bootstrap 5.
Bisa dijalankan di XAMPP/Laragon, dibuka dari HP/laptop lain lewat jaringan LAN, atau
di-deploy ke Vercel.

Fitur:

- **Login** dengan dua role: **admin** dan **kasir**
- **Dashboard**: transaksi & omzet hari ini, stok menipis, obat mendekati kadaluarsa,
  obat terlaris (admin)
- **Kasir**: cari obat dengan ketik nama/kode lalu Enter, hitung total & kembalian
  otomatis, cetak struk
- **Riwayat penjualan** dengan filter tanggal, metode bayar, dan kasir; admin bisa
  membatalkan transaksi dan stok otomatis kembali
- **Data obat**: pencarian, filter, sorting, golongan obat, harga beli/jual, kadaluarsa
- **Atur stok**: barang masuk, barang keluar/rusak, stok opname
- **Master data** (admin): kategori, supplier, user
- Stok aman dari oversell: baris stok dikunci selama transaksi, dan harga selalu
  diambil dari master obat

Aset CSS/JS (Bootstrap 5.3 + Bootstrap Icons) sudah disimpan di `public/vendor`, jadi
tampilan tetap normal tanpa internet dan tidak perlu `npm install`.

---

## 1. Menjalankan di lokal (XAMPP / Laragon)

Prasyarat: PHP 8.2+, Composer, MySQL/MariaDB.

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database `apotek_db` (lewat phpMyAdmin atau SQL di bawah), lalu sesuaikan `.env`:

```sql
CREATE DATABASE apotek_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apotek_db
DB_USERNAME=root
DB_PASSWORD=
```

Buat tabel dan isi data contoh, lalu jalankan:

```bash
php artisan migrate --seed
php artisan serve
```

Buka <http://localhost:8000>.

### Akun contoh

| Email | Password | Role |
|---|---|---|
| `admin@apotek.test` | `Admin12345` | admin |
| `kasir1@apotek.test` | `Kasir12345` | kasir |
| `kasir2@apotek.test` | `Kasir12345` | kasir |

Password awal bisa diatur lewat env `SEED_ADMIN_PASSWORD` / `SEED_KASIR_PASSWORD`
sebelum menjalankan seeder. **Ganti password setelah login pertama** lewat menu
nama user → *Ganti Password*.

### Alternatif: impor file SQL

Tersedia dump siap impor di [`database/sql/apotek_db.sql`](database/sql/apotek_db.sql),
berisi `CREATE DATABASE`, semua tabel (termasuk `sessions` & `cache` untuk login),
catatan migrasi, dan data contoh (3 user, 11 kategori, 5 supplier, 43 obat, 8 transaksi).

```bash
mysql -u root -p < database/sql/apotek_db.sql
```

Atau lewat phpMyAdmin → tab *Import*. Setelah impor, `php artisan migrate` tidak akan
mencoba membuat ulang tabel.

### Identitas apotek

Nama, alamat, dan telepon yang tampil di header dan struk diatur di `.env`:

```env
APOTEK_NAMA="Apotek Sehat"
APOTEK_ALAMAT="Jl. Contoh No. 1"
APOTEK_TELEPON=021-1234567
```

---

## 2. Membuka dari HP / komputer lain (LAN)

1. Cari IP laptop server: jalankan `ipconfig`, lihat **IPv4 Address** (mis. `10.50.0.133`).
2. Jalankan server supaya menerima koneksi dari jaringan (wajib `--host=0.0.0.0`):
   ```bash
   php artisan serve --host=0.0.0.0 --port=8090
   ```
3. Dari HP/laptop lain di Wi-Fi yang sama, buka `http://<IP-laptop>:8090`.

Catatan:

- **Firewall Windows** memblokir koneksi masuk pada jaringan *Public*. Buka portnya
  sekali lewat PowerShell **as Administrator**:
  ```powershell
  New-NetFirewallRule -DisplayName "Apotek 8090" -Direction Inbound -Protocol TCP -LocalPort 8090 -Action Allow -Profile Private,Public
  ```
  Kalau Wi-Fi-nya milik sendiri, lebih baik ubah profil jaringan ke *Private*.
- **IP bisa berubah** saat laptop tersambung ulang ke Wi-Fi. Cek lagi dengan `ipconfig`,
  atau atur IP tetap / DHCP reservation di router.
- Kalau port 8090 sudah dipakai aplikasi lain, ganti ke port lain (mis. `8091`).
- `php artisan serve` adalah server development, cukup untuk beberapa kasir. Untuk dipakai
  terus-menerus, lebih stabil lewat Apache XAMPP yang diarahkan ke folder `public/`.

---

## 3. Hak akses

| Menu / aksi | admin | kasir |
|---|:---:|:---:|
| Dashboard | ✓ (seluruh apotek) | ✓ (angka milik sendiri) |
| Kasir, simpan transaksi, cetak struk | ✓ | ✓ |
| Riwayat penjualan | semua kasir | milik sendiri |
| Batalkan transaksi (stok dikembalikan) | ✓ | – |
| Lihat data & detail obat | ✓ | ✓ (tanpa harga beli) |
| Tambah/ubah/hapus obat, atur stok | ✓ | – |
| Kategori, supplier, user | ✓ | – |
| Ganti password sendiri | ✓ | ✓ |

Pengaman lain:

- Password disimpan sebagai hash bcrypt: minimal 8 karakter, wajib huruf dan angka.
- Login gagal selalu menampilkan "Email atau password salah." supaya tidak bisa dipakai
  menebak email mana yang terdaftar. Percobaan login dibatasi 5×/menit per email + IP
  (`LOGIN_RATE_LIMIT`).
- User yang dinonaktifkan admin langsung keluar pada klik berikutnya. Password yang
  direset admin memutus sesi user tersebut di semua perangkat.
- Admin tidak bisa menghapus, menonaktifkan, atau menurunkan role akunnya sendiri, dan
  sistem selalu menyisakan minimal satu admin aktif.
- Menghapus kasir tidak menghapus riwayat penjualannya.
- Kasir yang membuka struk kasir lain mendapat halaman 404.
- Semua form memakai CSRF token.

---

## 4. Struktur data

| Tabel | Isi |
|---|---|
| `users` | Akun petugas: nama, email, password (hash), `role` (`admin`/`kasir`), `aktif` |
| `kategori` | Golongan produk apotek (analgesik, antibiotik, alkes, dst.) |
| `supplier` | Distributor/PBF beserta kontaknya |
| `obat` | Master obat: kode, harga beli/jual, stok, kadaluarsa, golongan obat |
| `penjualan` | Header transaksi: kasir (`user_id`), kode, total, bayar, kembalian, metode bayar |
| `detail_penjualan` | Item per transaksi (nama & harga di-*snapshot* saat transaksi) |
| `sessions`, `cache` | Session login & batas percobaan login (tabel bawaan Laravel) |

Catatan desain:

- `obat` memakai **soft delete** supaya riwayat penjualan lama tidak ikut rusak saat obat
  dihapus.
- `detail_penjualan` menyimpan salinan `nama_obat` dan `harga_satuan`, sehingga struk lama
  tetap benar walau master obat diedit belakangan.
- Stok tidak bisa diubah dari form *Ubah Obat*, hanya lewat **Atur Stok** atau penjualan,
  supaya tidak menimpa penjualan yang terjadi saat form sedang dibuka.
- `golongan` mengikuti penggolongan obat: bebas, bebas terbatas, keras, narkotika,
  psikotropika, herbal.

---

## 5. Deploy ke Vercel

### 5.1 Siapkan database MySQL

Vercel tidak menyediakan MySQL, jadi databasenya harus di layanan lain, misalnya:

| Layanan | Free tier (dicek September 2026) | Setting TLS di Vercel |
|---|---|---|
| **TiDB Cloud Starter** | 5 GiB data + 50 juta Request Unit per bulan per instance (maks. 5 instance), tanpa kartu kredit, ada region Singapore | TLS wajib. Pakai CA sistem: `MYSQL_ATTR_SSL_CA=/etc/pki/tls/certs/ca-bundle.crt` |
| **Aiven for MySQL** | 1 GB storage, 1 GB RAM, tanpa kartu kredit; server mati otomatis kalau lama tidak dipakai | Unduh `ca.pem` di Console → Overview service, simpan di `certs/ca.pem`, lalu `MYSQL_ATTR_SSL_CA=/var/task/user/certs/ca.pem` |

> MySQL lokal (XAMPP/Laragon) **tidak bisa** dipakai dari Vercel: `DB_HOST=127.0.0.1`
> di Vercel menunjuk ke server Vercel itu sendiri, bukan ke komputer Anda.

Setelah database jadi, buat tabelnya dari komputer lokal (arahkan `.env` ke kredensial
cloud) atau impor `database/sql/apotek_db.sql`:

```bash
php artisan migrate --seed --force
```

### 5.2 File yang sudah disiapkan

| File | Fungsi |
|---|---|
| [`vercel.json`](vercel.json) | Runtime `vercel-php@0.9.0` (PHP 8.5). File statis di `public/` (CSS, ikon) dilayani langsung; request lain diteruskan ke Laravel |
| [`api/index.php`](api/index.php) | Entry point serverless; memindahkan `storage/` & `bootstrap/cache` ke `/tmp` |
| [`.vercelignore`](.vercelignore) | Menahan `.env`, `vendor`, database lokal ikut terkirim |

Filesystem Vercel **read-only** kecuali `/tmp`, karena itu `api/index.php` mengeset
`LARAVEL_STORAGE_PATH` dan variabel `APP_*_CACHE` ke `/tmp` sebelum Laravel di-boot.
`composer install` dijalankan otomatis oleh runtime saat build.

Folder `api/` hanya nama yang diwajibkan Vercel untuk serverless function. Aplikasinya
tetap aplikasi web Laravel biasa, bukan REST API.

### 5.3 Deploy

Push ke GitHub → **Import Project** di dashboard Vercel (framework preset: *Other*),
atau lewat CLI:

```bash
npm i -g vercel
vercel login
vercel --prod
```

### 5.4 Environment variables di Vercel

Set di **Project → Settings → Environment Variables**:

```env
APP_NAME=Apotek
APP_ENV=production
APP_KEY=base64:xxxxx        # ambil dari output `php artisan key:generate --show`
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://nama-project.vercel.app

APOTEK_NAMA="Apotek Sehat"
APOTEK_ALAMAT="Jl. Contoh No. 1"

DB_CONNECTION=mysql
DB_HOST=host-mysql-anda
DB_PORT=4000                # TiDB memakai 4000, MySQL biasa 3306
DB_DATABASE=apotek_db
DB_USERNAME=user
DB_PASSWORD=password

LOG_CHANNEL=stderr          # log masuk ke Vercel Function Logs
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

**Wajib:** `APP_KEY` harus diisi, dan `SESSION_DRIVER` harus `database` (atau `cookie`).
Driver `file` tidak cocok karena session hilang setiap kali Vercel memakai instance baru,
sehingga pengguna terus terlempar ke halaman login.

Variabel lama `API_KEY`, `API_RATE_LIMIT`, dan `TOKEN_EXPIRE_HOURS` sudah tidak dipakai
dan boleh dihapus.

Kalau provider MySQL memaksa TLS:

- Punya file CA → simpan di `certs/ca.pem`, set `MYSQL_ATTR_SSL_CA=/var/task/user/certs/ca.pem`
  (root proyek di runtime Vercel adalah `/var/task/user`)
- Tidak punya file CA → set `DB_SSL_VERIFY=false` (koneksi tetap terenkripsi, sertifikat tidak diverifikasi)

### 5.5 Troubleshooting

**`No Output Directory named "dist" found after the Build completed`**

Skeleton Laravel membawa `package.json` + `vite.config.js`, sehingga Vercel mengira ini
proyek Vite dan mencari folder `dist`. Proyek ini tidak memakai Vite, jadi `vercel.json`
menimpa pengaturan tersebut (`framework: null`, `installCommand: ""`,
`outputDirectory: "public"`) dan `.vercelignore` menahan `package.json` / `vite.config.js`.
Nilai di `vercel.json` selalu menang atas Project Settings di dashboard.

**`date_default_timezone_set(): Timezone ID '' is invalid`** (atau error lain soal nilai `[]`)

Ada Environment Variable di Vercel yang **dibuat tapi nilainya kosong**. Config proyek ini
memakai `env('X') ?: 'default'` untuk variabel yang fatal kalau kosong:

| Variabel | Fallback kalau kosong |
|---|---|
| `APP_TIMEZONE` | `Asia/Jakarta` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `DB_CONNECTION` | `mysql` |
| `LOGIN_RATE_LIMIT` | `5` |
| `DB_TIMEOUT` | `10` |

Tetap rapikan dashboard: hapus variabel yang tidak dipakai, dan pastikan `APP_KEY`
serta `DB_HOST` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` benar-benar terisi.

**Tampilan berantakan (CSS tidak termuat)**

Buka `https://<domain>/vendor/bootstrap/bootstrap.min.css`. Kalau hasilnya bukan file CSS,
pastikan `vercel.json` masih memuat `{ "handle": "filesystem" }` sebelum route terakhir.

---

## 6. Testing

```bash
php artisan test
```

41 test mencakup login/logout, batas percobaan login, akun nonaktif, ganti password,
CRUD obat/kategori/supplier/user, atur stok, transaksi penjualan (stok kurang, uang
kurang, item digabung, harga dari master obat, rollback), pembatalan transaksi, dan
pembatasan akses per role.

Untuk mencoba aplikasi tanpa MySQL, tersedia `.env.testing` berbasis SQLite:

```bash
php artisan --env=testing migrate:fresh --seed
php artisan serve --env=testing
```

---

## 7. Struktur proyek

```
app/Http/Controllers/Auth/LoginController.php   Login & logout
app/Http/Controllers/                           Dashboard, Obat, Kategori, Supplier,
                                                Penjualan (kasir & struk), User, Password
app/Http/Middleware/EnsureRole.php              role:admin / role:admin,kasir
app/Http/Middleware/EnsureUserIsActive.php      Keluarkan user yang dinonaktifkan
app/Http/Requests/                              Validasi form
app/Models/                                     User, Obat, Kategori, Supplier, Penjualan, DetailPenjualan
resources/views/                                Tampilan Blade (layouts, obat, penjualan, ...)
public/vendor/                                  Bootstrap 5.3 + Bootstrap Icons (offline)
public/css/app.css                              Gaya tambahan
routes/web.php                                  Daftar halaman & hak akses
config/apotek.php                               Identitas apotek & batas login
database/migrations/                            Skema tabel
database/seeders/                               Data contoh
database/sql/apotek_db.sql                      Dump MySQL siap impor
tests/Feature/                                  Test halaman, form, dan hak akses
api/index.php, vercel.json                      Deploy ke Vercel
```
