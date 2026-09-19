<p align="center"><img src="public/images/logo.png" width="150" alt="CAMAR Logo"></p>

# Dokumentasi Proyek CAMAR (Carbon Offset Platform)

## 1. Ikhtisar Proyek (Project Overview)
**CAMAR** adalah sebuah platform berbasis web yang dirancang untuk memfasilitasi transaksi perdagangan karbon (Carbon Offset). Platform ini mempertemukan pihak penyedia proyek karbon (*Seller*) dengan pihak yang ingin mengompensasi jejak karbon mereka (*Buyer*). Selain itu, sistem dilengkapi dengan Kalkulator Emisi Karbon dan panel administrasi yang *real-time* untuk mengawasi seluruh aktivitas dan verifikasi di dalam aplikasi.

---

## 2. Panduan Instalasi Lokal & Menjalankan Proyek

Proyek ini telah dikonfigurasi dengan *shortcut* Composer untuk mempermudah proses instalasi dan pengembangan lokal.

### Persyaratan Sistem
- **PHP** >= 8.2
- **Composer** (terbaru)
- **Node.js** & **NPM**
- **MySQL** / **MariaDB** (atau SQLite untuk kemudahan)

### Langkah-langkah Instalasi
1. **Clone repositori** ini ke dalam mesin lokal Anda:
   ```bash
   git clone <url-repo>
   cd camar
   ```
2. **Jalankan skrip Setup Otomatis**:
   Proyek ini dilengkapi dengan skrip `setup` yang akan menginstal dependensi, menyalin `.env`, men-generate key, menjalankan migrasi database, dan mem-build aset frontend (Vite).
   ```bash
   composer run setup
   ```
   *(Catatan: Pastikan Anda telah mengonfigurasi koneksi database Anda di file `.env` sebelum menjalankan perintah ini, atau biarkan default jika menggunakan SQLite)*.

3. **Menjalankan Server Pengembangan (Development Server)**:
   Gunakan perintah berikut untuk menjalankan server PHP, antrean pekerjaan (Queue), dan Vite bundler secara bersamaan dalam satu terminal:
   ```bash
   composer run dev
   ```
   Perintah ini akan menggunakan `concurrently` untuk melayani:
   - `php artisan serve` (Server aplikasi di http://127.0.0.1:8000)
   - `php artisan queue:listen` (Memproses antrean di background)
   - `npm run dev` (Hot Module Replacement untuk Tailwind CSS & JS)

### Konfigurasi Tambahan (.env)
Pastikan Anda telah mengisi *keys* untuk layanan eksternal di dalam `.env` Anda:
- **Database**: Ubah `DB_CONNECTION`, `DB_DATABASE`, dll sesuai sistem Anda.
- **Midtrans**: Isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY`.
- **Reverb (WebSocket)**: Secara default, `php artisan install:broadcasting` (atau setup Reverb) sudah akan mengisi `REVERB_APP_ID`, `REVERB_APP_KEY`, dan `REVERB_APP_SECRET`.

---

## 3. Tech Stack & Dependensi Utama
- **Bahasa & Framework**: PHP 8.2+, Laravel 12
- **Frontend / Styling**: Blade Templating, Tailwind CSS v4, Vanilla JavaScript
- **Bundler**: Vite
- **Database**: MySQL / SQLite (via Eloquent ORM)
- **Real-Time WebSockets**: Laravel Reverb & Laravel Echo (Pusher-compatible)
- **Payment Gateway**: Midtrans (Snap Token & Webhook API)
- **Auth & API**: Laravel Sanctum (untuk API Mobile/Eksternal)

---

## 4. Hak Akses & Peran (*Roles*)
Platform ini menerapkan sistem *Multi-Role* yang diatur melalui middleware khusus:

1. **Buyer (Pembeli)**
   - Dapat berupa **Individu** (Personal) atau **Perusahaan** (Company).
   - Dapat menghitung emisi karbon (wajib sebelum membeli).
   - Menjelajahi proyek karbon, menambah ke keranjang (*Cart*), dan melakukan Checkout.
   - Mengelola transaksi dan mengunduh sertifikat *Carbon Offset*.

2. **Seller (Penjual / Penyedia Proyek)**
   - Wajib berbentuk **Perusahaan** dan mengunggah dokumen sertifikasi standar (misal: *Gold Standard* atau *VCS*).
   - Membuat, mengedit, dan mengelola katalog Proyek Karbon.
   - Memantau transaksi yang masuk untuk proyek-proyek mereka.

3. **Admin / Super Admin / Auditor**
   - **Admin/Super Admin**: Mengelola peran (*role/permissions*), konfigurasi sistem, dan pengguna.
   - **Auditor**: Memverifikasi validitas proyek baru dari Seller dan memverifikasi dokumen entitas.
   - Mengakses panel kontrol (*Control Center*) secara *real-time* (notifikasi pendaftaran baru dan transaksi).

---

## 5. Struktur Database & Model (Core Models)

Sistem menggunakan Eloquent ORM dengan model-model utama berikut yang berada di `app/Models/`:

- **`User`**: Mengelola seluruh pengguna (buyers, sellers, admins). Menyimpan data autentikasi, status akun (`pending`, `verified`, `suspended`), dan JSON metadata dokumen.
- **`Project`**: Mewakili entitas proyek karbon yang dijual. Memiliki atribut kapasitas/stok ton karbon, harga per ton (`price_per_ton`), dan status persetujuan.
- **`Order`** / **`Transaction`**: Menyimpan data pembelian kredit karbon. Mencatat relasi antara *Buyer* dan *Project*, jumlah (*quantity*), pajak, serta status pembayaran (`pending`, `paid`, `cancelled`, dll).
- **`EmissionCalculation`**: Menyimpan hasil hitung jejak karbon dari pengguna.
- **`DocumentVerification`**: Mengatur status verifikasi masing-masing dokumen legal (NPWP, NIB, Akta, dll) yang diunggah oleh entitas Perusahaan/Seller.
- **`Role` & `Permission`**: Struktur otorisasi khusus untuk granularitas hak akses panel kontrol (misalnya: *users.verify*, *projects.verify*).
- **`AdminLoginLog` & `AdminActivityLog`**: Pencatatan jejak audit (*Audit Trail*) untuk aktivitas administrator di back-office.

---

## 6. Alur Kerja Utama (*Main Workflows*)

### A. Alur Registrasi & Verifikasi (*Onboarding*)
1. Pengguna memilih tipe peran (Buyer atau Seller) dan kategori (Personal atau Company).
2. Form multi-step (dengan validasi SweetAlert) mengumpulkan data profil, *password*, dan dokumen pendukung.
3. Setelah *submit*, foto profil di-crop dan disimpan menggunakan Base64 (via Javascript Cropper.js).
4. Akun diset sebagai `pending`. Notifikasi disebarkan (*broadcast*) ke *Admin Dashboard* menggunakan WebSocket (Reverb).
5. Admin melakukan verifikasi manual terhadap dokumen melalui *Admin Panel*. Jika lolos, status user diubah menjadi `verified`.

### B. Alur Kalkulasi & Pembelian (*Checkout Flow*)
1. Buyer melakukan perhitungan emisi karbon di halaman Kalkulator (wajib sebelum membeli).
2. Buyer menelusuri halaman Proyek dan memasukkan proyek ke *Cart* (Session-based) atau klik *Buy Now*.
3. Pada halaman *Checkout*, fungsi `OrderController@store` menyimpan transaksi ke database dengan status `pending` dan *order_number* unik.
4. Pengguna diteruskan ke halaman konfirmasi yang me-*render* **Midtrans Snap Popup** menggunakan *Snap Token* hasil integrasi Rest API.
5. Setelah dibayar, Midtrans mengirimkan notifikasi asinkron ke *Webhook* (`/orders/midtrans/notification`).
6. Aplikasi memvalidasi `signature_key` dari Midtrans dan mengubah status pesanan menjadi `paid`, kemudian secara otomatis mengurangi `stock_available` dari proyek.

---

## 7. Integrasi Eksternal (3rd Party Services)

### Midtrans (Payment Gateway)
- Konfigurasi diletakkan di `config/midtrans.php` atau `.env`.
- Pengelolaan dilakukan di `OrderController.php`. Webhook Midtrans dikecualikan dari middleware proteksi CSRF (diatur dalam `bootstrap/app.php`).

### Laravel Reverb (Realtime WebSocket)
- Digunakan untuk notifikasi seketika di halaman *Admin Dashboard* (seperti pendaftaran user baru).
- Dikonfigurasi melalui `config/broadcasting.php`. Channel didengarkan (*listened*) melalui Laravel Echo di bagian *frontend*.

---

## 8. Struktur Direktori Kunci
| Direktori | Deskripsi |
|-----------|-----------|
| `app/Http/Controllers/` | Memuat logika bisnis aplikasi (Auth, Cart, Order, Project, Chatbot, dll). |
| `app/Http/Controllers/Api/` | Endpoint khusus yang melayani format JSON (dilindungi oleh Sanctum). |
| `app/Http/Controllers/Admin/` | Controller spesifik untuk area *Control Center* / Panel Admin. |
| `resources/views/main_page/` | Menyimpan template antarmuka, dibagi menjadi sub-direktori fungsional (`admin-panel/`, `projects/`, `login/`). |
| `routes/web.php` & `api.php` | Konfigurasi pemetaan rute. Route web menggunakan proteksi CSRF dan rate limit tambahan. |

---

> [!TIP]
> **Praktik Pengembangan**: Ketika membuat perubahan pada CSS (terutama kelas Tailwind baru), pastikan server `vite` aktif (sudah tergabung di dalam perintah `composer run dev`). Kode-kode formating sangat disarankan untuk dirapikan mengikuti standar *Laravel Pint* menggunakan perintah `vendor/bin/pint`.
