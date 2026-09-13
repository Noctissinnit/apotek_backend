<?php

return [
    /*
    | Kunci untuk endpoint tulis. Kosongkan saat development supaya API bebas
    | dicoba; isi lewat env di production (Vercel > Settings > Environment Variables).
    */
    'api_key' => env('API_KEY'),

    /*
    | Batas request per menit per IP untuk seluruh route /api.
    */
    'rate_limit' => (int) env('API_RATE_LIMIT', 60),
];
