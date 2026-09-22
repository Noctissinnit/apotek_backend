<?php

return [
    /*
    | Batas percobaan login per menit untuk kombinasi email + IP,
    | untuk menahan tebak-tebakan password.
    */
    // Nilai kosong/0 jangan sampai jadi limit 0 (semua login ditolak).
    'login_rate_limit' => max(1, (int) (env('LOGIN_RATE_LIMIT') ?: 5)),

    /*
    | Nama apotek untuk judul halaman dan kop struk.
    */
    'nama' => env('APOTEK_NAMA') ?: 'Apotek Sehat',
    'alamat' => env('APOTEK_ALAMAT') ?: 'Jl. Contoh No. 1',
    'telepon' => env('APOTEK_TELEPON') ?: '-',
];
