<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema tabel users untuk platform CAMAR.
     *
     * Kolom dibagi menjadi 4 kelompok:
     *   A. Bersama     — semua pengguna (company & personal)
     *   B. Company     — hanya account_category = 'company'
     *   C. Personal    — hanya account_category = 'personal'
     *   D. Sistem      — role, status, dokumen, foto
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // ------------------------------------------------
            // A. KOLOM BERSAMA
            // ------------------------------------------------
            $table->string('name');                         // Nama PIC (company) / Nama lengkap (personal)
            $table->string('email')->unique();              // Email utama login
            $table->string('password');
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();

            // ------------------------------------------------
            // B. KOLOM COMPANY ONLY
            // ------------------------------------------------
            $table->string('company_name')->nullable();
            // $table->string('company_email')->nullable();   // Email resmi perusahaan (bisa beda dgn login email)
            $table->string('industry', 100)->nullable();
            $table->string('position', 100)->nullable();   // Jabatan PIC di perusahaan

            // ------------------------------------------------
            // C. KOLOM PERSONAL ONLY
            // ------------------------------------------------
            $table->string('job_title', 100)->nullable();  // Pekerjaan individu

            // ------------------------------------------------
            // D. KOLOM SISTEM
            // ------------------------------------------------

            /**
             * role: buyer | seller
             * Menentukan apa yang bisa dilakukan user di platform
             */
            $table->enum('role', ['buyer', 'seller', 'admin', 'super_admin'])->default('buyer');

            /**
             * account_category: company | personal
             * Menentukan jenis pengguna dan dokumen yang dibutuhkan
             */
            $table->enum('account_category', ['company', 'personal'])->default('personal');

            /**
             * status: pending | verified | rejected
             * - pending  : baru mendaftar, menunggu verifikasi admin
             * - verified : admin sudah approve, bisa akses penuh
             * - rejected : ditolak admin
             */
            $table->enum('status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending');

            /**
             * documents: JSON
             * Menyimpan path semua dokumen yang diupload.
             * Contoh isi:
             * {
             *   "npwp": "documents/1/npwp_1_1234567890.pdf",
             *   "akta": "documents/1/akta_1_1234567890.pdf",
             *   "nib":  "documents/1/nib_1_1234567890.pdf",
             *   "iso":  "documents/1/iso_1_1234567890.pdf",
             *   "gold_standard": "documents/1/gold_standard_1_1234567890.pdf",
             *   "vcs":  "documents/1/vcs_1_1234567890.pdf"
             * }
             */
            $table->json('documents')->nullable();

            /**
             * profile_photo: path relatif ke storage/app/public/
             * Diambil dengan Storage::url($user->profile_photo)
             */
            $table->string('profile_photo')->nullable();

            // Timestamps standar Laravel
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); // Untuk keamanan, data tidak langsung dihapus permanen
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
