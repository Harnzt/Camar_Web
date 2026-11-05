<p align="center"><img src="public/images/logo.png" width="150" alt="CAMAR Logo"></p>

# Dokumentasi Proyek CAMAR (Retail Carbon Offset Platform)

## 1. Ikhtisar Proyek (Project Overview)
**CAMAR (Carbon Market & Offset Retail)** adalah platform *retail carbon offset* berbasis web yang memfasilitasi pembelian kredit karbon (*carbon credit*) secara langsung oleh **Buyer Individu** maupun **Buyer Perusahaan** di Indonesia.

Berbeda dengan marketplace dua sisi tradisional, CAMAR beroperasi dengan **model Retail Terpusat**:
- Seluruh katalog **Proyek Karbon** dikurasi, dikelola, dan disediakan secara langsung oleh **Admin / Super Admin CAMAR**, serta diverifikasi oleh **Auditor**.
- Pengguna publik mendaftar sebagai **Buyer** (**Individu** atau **Perusahaan**) untuk menghitung jejak emisi karbon mereka (berstandar *GHG Protocol Scope 1, 2, dan 3*), mendapatkan rekomendasi proyek offset yang sesuai, melakukan pembelian kredit karbon secara retail melalui **Midtrans**, dan memperoleh **Sertifikat Carbon Offset** resmi.
- Dilengkapi dengan **CAMI (CAMAR Intelligence)**, asisten chatbot berbasis Rasa yang terintegrasi untuk memandu kalkulasi emisi (termasuk mode *Guest / Sesi Tamu*), simulasi biaya offset, dan konsultasi proyek karbon.

---

## 2. Panduan Instalasi Lokal & Menjalankan Proyek

Proyek ini telah dikonfigurasi dengan *shortcut* Composer untuk mempermudah proses instalasi dan pengembangan lokal.

### Persyaratan Sistem
- **PHP** >= 8.2
- **Composer** (terbaru)
- **Node.js** & **NPM**
- **MySQL** / **MariaDB** (`camar_kel1`)
- **Python 3.10+** & **Rasa** *(opsional, untuk menjalankan layanan chatbot CAMI)*

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
   *(Catatan: Pastikan Anda telah mengonfigurasi koneksi database MySQL Anda di file `.env` sebelum menjalankan perintah ini).*

3. **Menjalankan Server Pengembangan (Development Server)**:
   Gunakan perintah berikut untuk menjalankan server PHP, antrean pekerjaan (Queue), dan Vite bundler secara bersamaan dalam satu terminal:
   ```bash
   composer run dev
   ```
   Perintah ini menjalankan:
   - `php artisan serve` (Server aplikasi di `http://127.0.0.1:8000`)
   - `php artisan queue:listen` (Memproses antrean di background)
   - `npm run dev` (Hot Module Replacement untuk CSS & JS)

### Konfigurasi Tambahan (`.env`)
Pastikan Anda telah mengisi konfigurasi berikut di dalam `.env`:
- **Database**: `DB_CONNECTION=mysql`, `DB_DATABASE=camar_kel1`, `DB_USERNAME`, dan `DB_PASSWORD`.
- **Midtrans**: Isi `MIDTRANS_ID`, `MIDTRANS_SERVER_KEY`, dan `MIDTRANS_CLIENT_KEY`.
- **Rasa Chatbot**: Pastikan `RASA_WEBHOOK_URL` mengarah ke server Rasa (`http://127.0.0.1:5005/webhooks/rest/webhook`).
- **Reverb (WebSocket)**: Konfigurasi `REVERB_APP_ID`, `REVERB_APP_KEY`, dan `REVERB_APP_SECRET` untuk notifikasi *real-time* pada panel admin.

---

## 3. Tech Stack & Dependensi Utama
- **Bahasa & Framework**: PHP 8.2+, Laravel 12
- **Frontend / Styling**: Blade Templating, Tailwind CSS v4, Custom CSS, Vanilla JavaScript
- **Bundler**: Vite
- **Database**: MySQL (via Eloquent ORM) + Dataset Bandara (`airports_data`) untuk kalkulasi rute penerbangan Haversine
- **Chatbot Engine**: Rasa Open Source & Rasa SDK (`CAMI_CHATBOT`) terintegrasi dengan layanan internal Laravel
- **Real-Time WebSockets**: Laravel Reverb & Laravel Echo (Pusher-compatible)
- **Payment Gateway**: Midtrans (Snap Token & Webhook Notification API)
- **Auth & API**: Session Auth & Laravel Sanctum (untuk endpoint API Mobile/Eksternal)

---

## 4. Hak Akses & Peran (*Roles*)
Dalam model retail CAMAR, hak akses dibagi menjadi:

1. **Buyer (Pembeli Retail)**
   - Terbagi menjadi dua kategori akun:
     - **Individu (`personal`)**: Mendaftar dengan data identitas diri, pekerjaan (pilihan terstruktur), alamat wilayah Indonesia (Negara, Provinsi, Kabupaten/Kota, Kecamatan, Kode Pos, Alamat Lengkap), dan dokumen NPWP.
     - **Perusahaan (`company`)**: Mendaftar dengan data instansi/perusahaan, PIC, jabatan, industri, alamat wilayah lengkap, serta dokumen legalitas wajib (Akta Pendirian, NPWP, NIB/SIUP) dan opsional (ISO 14001).
   - Menghitung jejak emisi karbon pada **Kalkulator Karbon** atau melalui **Chatbot CAMI** (wajib memiliki kalkulasi emisi sebelum melakukan pembelian kredit karbon).
   - Menjelajahi katalog proyek retail, mendapatkan rekomendasi proyek berbasis kecocokan profil emisi, menambahkan kredit karbon ke keranjang (*Cart*), dan melakukan *Checkout*.
   - Memantau saldo karbon (*Carbon Balance*), riwayat transaksi, dan mengunduh **Sertifikat Carbon Offset**.

2. **Admin & Super Admin (Pengelola Retail)**
   - **Manajemen Katalog Proyek Retail**: Menambahkan, mengedit, memperbarui stok/harga, dan menghapus proyek karbon secara langsung melalui *Admin Panel* (`/admin/projects`).
   - **Verifikasi Buyer & Dokumen**: Memeriksa pendaftaran akun Buyer baru serta memverifikasi dokumen legalitas (NPWP, Akta, NIB, ISO).
   - **Manajemen Transaksi & Sertifikat**: Memantau pembayaran kredit karbon dan menerbitkan sertifikat offset.
   - **Manajemen Administrator & Hak Akses** *(khusus Super Admin)*: Mengelola akun admin/auditor, *role permissions*, dan memantau log login serta jejak audit (*Audit Trail*).

3. **Auditor (Auditor Pemerintah / Independen)**
   - Memverifikasi kelayakan, standar metodologi, dan keabsahan proyek karbon yang akan ditayangkan di katalog retail.
   - Memeriksa dokumen verifikasi pengguna dan meninjau log audit kepatuhan (*compliance*).

*(Catatan: Peran `seller` telah dinonaktifkan pada alur login dan registrasi publik karena penyediaan stok kredit karbon dikelola langsung secara retail oleh platform CAMAR).*

---

## 5. Fitur Utama Platform

### A. Kalkulator Emisi Karbon (*GHG Protocol Scope 1, 2, & 3*)
Kalkulator karbon di website (`/kalkulator`) dan chatbot CAMI menggunakan faktor emisi yang telah diselaraskan (mengacu pada KLHK, Kementerian ESDM, IPCC 2006, DEFRA, dan PT KAI):
- **Buyer Individu (`personal`)**:
  - **Scope 1**: Konsumsi energi rumah tangga (LPG, CNG) dan kendaraan pribadi (Mobil Bensin/Diesel, Sepeda Motor).
  - **Scope 2**: Konsumsi listrik rumah tangga menggunakan faktor emisi rata-rata nasional PLN (`0,85 kgCO₂e/kWh`).
  - **Scope 3**: Transportasi umum (Bus, Kereta, Ojek Online/Ojol, dan Pesawat dengan perhitungan jarak otomatis berbasis koordinat bandara *Haversine*), konsumsi pangan, penggunaan air bersih, dan timbulan sampah rumah tangga.
- **Buyer Perusahaan (`company`)**:
  - **Scope 1 — Pembakaran Stasioner**: 14 jenis bahan bakar industri/genset/boiler (Minyak Solar CN53/CN51/CN48, Minyak Diesel, Minyak Bakar MFO/HFO, Bensin RON98/92/90/88, Minyak Tanah, LPG, Gas Alam, CNG, dan Batubara) dengan satuan dinamis (`Liter/thn`, `kg/thn`, `Nm³/thn`).
  - **Scope 1 — Pembakaran Bergerak**: Armada kendaraan operasional berdasarkan konsumsi BBM (`Liter/thn`) atau jarak tempuh (`km/thn`).
  - **Scope 2 — Listrik PLN Wilayah Grid**: Faktor emisi spesifik per jaringan interkoneksi wilayah Kementerian ESDM (**Grid Jawa-Bali**, **Grid Sumatera**, **Grid Kalimantan**, **Grid Sulawesi**, serta **Grid Maluku & Papua**).
  - **Scope 3 — Perjalanan Dinas**: Penerbangan dinas (otomatis menghitung jarak bandara asal-tujuan × jumlah penumpang × kelas kabin), akomodasi hotel (`kamar × malam`), dan perjalanan kereta api KAI (7 kelas kereta × jumlah penumpang/pax × jarak tempuh).

### B. Chatbot Asisten Karbon "CAMI"
- Mendukung **Sesi Tamu (*Guest Session*)** maupun **Buyer Terautentikasi**.
- Dapat menjalankan kalkulasi emisi Individu & Perusahaan secara percakapan, memberikan rekomendasi proyek yang paling cocok atau paling ekonomis untuk meng-offset sisa emisi di dashboard, menampilkan simulasi pembelian kredit karbon, serta menjawab pertanyaan seputar katalog proyek secara *real-time* dari database.

### C. Rekomendasi Proyek & Transaksi Retail
- **Sistem Rekomendasi (`ProjectRecommendationService`)**: Mencocokkan profil emisi Buyer (sektor dominan Scope 1/2/3 dan kebutuhan tonase) dengan kategori, lokasi, serta ketersediaan stok proyek karbon.
- **Pembelian & Pembayaran Midtrans**: Mendukung pembelian multi-proyek via keranjang (*Cart*) maupun pembelian langsung (*Checkout*), terintegrasi dengan **Midtrans Snap** dan *Webhook Notification* otomatis.
- **Sertifikat Carbon Offset**: Diterbitkan untuk setiap transaksi yang telah selesai diverifikasi sebagai bukti kontribusi pengurangan emisi karbon.

---

## 6. Struktur Database & Model Utama (`app/Models/`)
- **`User`**: Mengelola akun `buyer` (`personal` / `company`), `admin`, `auditor`, dan `super_admin`. Menyimpan data profil, pekerjaan (`job_title`), data wilayah terpisah (`country`, `province`, `regency`, `district`, `postal_code`), alamat gabungan (`address`), status verifikasi (`pending`, `verified`, `rejected`, `suspended`), serta metadata dokumen.
- **`Project`**: Menyimpan katalog proyek karbon retail yang dikelola Admin (kategori, lokasi, standar sertifikasi, harga per ton `price_per_ton`, stok tersedia `stock_available`, dan status persetujuan).
- **`EmissionCalculation`**: Menyimpan riwayat perhitungan emisi karbon pengguna beserta rincian *breakdown* per Scope (`scope1_kg`, `scope2_kg`, `scope3_kg`, `total_kg`, dan JSON `scope_details`).
- **`Order` & `Transaction`**: Mencatat pesanan pembelian kredit karbon retail, relasi dengan proyek, status pembayaran Midtrans, serta nomor dan data sertifikat offset.
- **`CartItem` & `Watchlist`**: Mengelola keranjang belanja kredit karbon dan daftar pantau proyek favorit Buyer.
- **`DocumentVerification`**: Mengelola status verifikasi masing-masing dokumen legalitas Buyer (NPWP, Akta, NIB, ISO).
- **`AdminLoginLog` & `AdminActivityLog`**: Mencatat riwayat login dan aktivitas administrator/auditor untuk kebutuhan audit keamanan.

---

## 7. Struktur Direktori Kunci
| Direktori | Deskripsi |
|-----------|-----------|
| `app/Http/Controllers/` | Controller utama aplikasi (`AuthController`, `CalculatorController`, `ProjectController`, `CartController`, `OrderController`, `BuyerDashboardController`, `ChatbotController`). |
| `app/Http/Controllers/Admin/` | Controller panel administrasi & manajemen retail (`ProjectManagementController`, `ProjectVerificationController`, `UserVerificationController`, `TransactionManagementController`, `AdminManagementController`, `AuditLogController`). |
| `app/Services/` | Layanan logika bisnis inti (`CarbonCalculationService`, `ProjectRecommendationService`, `ChatbotResultService`, `ChatbotProjectQueryService`, `ChatbotInformationService`). |
| `resources/views/main_page/` | Template antarmuka Blade untuk halaman publik, kalkulator, registrasi multi-step, dashboard buyer, dan `admin-panel/`. |
| `public/js/` & `public/css/` | Aset JavaScript (`calculator.js`, `register.js`, `chatbot.js`, dll.) dan stylesheet per modul halaman. |
| `routes/web.php` & `routes/api.php` | Definisi rute web (dilindungi CSRF & middleware role/permission) serta endpoint API Sanctum. |

---

> [!TIP]
> **Praktik Pengembangan**: Ketika mengubah aset frontend atau kelas Tailwind CSS, pastikan server `vite` aktif melalui `composer run dev`. Jika mengubah logika percakapan pada folder `CAMI_CHATBOT`, pastikan server `rasa run actions` (port `5055`) dan `rasa run --enable-api` (port `5005`) di-restart agar perubahan segera diterapkan.
