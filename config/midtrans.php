<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Daftarkan akun di https://dashboard.midtrans.com
    | Tambahkan ke .env:
    |
    |   MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
    |   MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
    |   MIDTRANS_IS_PRODUCTION=false
    |
    */

    'merchant_id'   => env('MIDTRANS_ID', ''),
    'server_key'    => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'    => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // URL Snap JS (otomatis pilih sandbox/production)
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',
];