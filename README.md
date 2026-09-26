<div align="center">

  <img src="public/images/logo-pcmsimo-warna.png" alt="Logo PCM Simo" width="220">

  # Portal & CMS Pimpinan Cabang Muhammadiyah Simo
  
  **Sistem Informasi & Content Management System (CMS) Resmi PCM Simo, Boyolali, Jawa Tengah**

  <p align="center">
    <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
    <img src="https://img.shields.io/badge/Inertia.js-v2-9553E9?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js">
    <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white" alt="Vue 3">
    <img src="https://img.shields.io/badge/Vuetify-3.x-1867C0?style=for-the-badge&logo=vuetify&logoColor=white" alt="Vuetify 3">
    <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  </p>

</div>

---

## 📖 Tentang Project

Aplikasi ini merupakan portal resmi dan sistem pengelolaan konten (CMS) untuk **Pimpinan Cabang Muhammadiyah (PCM) Simo**, Kabupaten Boyolali. Dikembangkan untuk mempublikasikan syiar dakwah, berita kegiatan persyarikatan, profil majelis/lembaga, amal usaha (AUM), serta menyediakan layanan informasi jadwal sholat secara *real-time* kepada masyarakat.

Dibangun dengan arsitektur modern berbasis **Laravel 12** sebagai *backend RESTful/Inertia controller* dan **Vue 3 + Vuetify 3** melalui **Inertia.js**, memberikan pengalaman pengguna aplikasi satu halaman (SPA) yang cepat, interaktif, dan responsif.

---

## ✨ Fitur Unggulan

### 1. 📰 Manajemen Berita & Konten Publikasi
- **TipTap Rich Text Editor**: Editor penulisan artikel interaktif dengan dukungan pemformatan lengkap.
- **WebP Image Converter**: Unggahan gambar otomatis dioptimasi dan dikonversi ke format WebP untuk kecepatan loading maksimal.
- **Kategori & Label Berita**: Pengelompokan artikel berbasis kategori dengan penanda warna (*hex color validation*).
- **Auto-Generated Safe Slug**: Pembuatan tautan URL ramah SEO yang unik dan mencegah tabrakan (*collision-free*).
- **Halaman Detail Interaktif**: Dilengkapi dengan estimasi waktu baca, navigasi artikel terkait, dan tombol bagikan.

### 2. 🕌 Integrasi Jadwal Sholat Real-Time
- Sinkronisasi otomatis data waktu sholat harian untuk wilayah Simo dan sekitarnya menggunakan **API Hisabmu**.
- Penanda waktu sholat berikutnya (*next prayer countdown*) secara akurat.

### 3. 👥 Multi-Role & Hak Akses Bertingkat
- **Super Admin**: Hak akses penuh mencakup manajemen pengguna, kategori, berita, dan konfigurasi sistem.
- **Admin**: Pengelolaan berita, media, dan kategori persyarikatan.
- **Tim Redaksi**: Penyusunan dan penulisan draf artikel kegiatan persyarikatan.

### 4. 🛡️ Keamanan & Optimasi
- **HTML Sanitization**: Pembersihan konten dari potensi serangan Stored XSS menggunakan whitelist tag terverifikasi.
- **Path Traversal Protection**: Validasi ketat pada sistem upload dan penghapusan berkas media.
- **Strong Password Policy**: Validasi kata sandi dengan standar kompleksitas minimal 8 karakter, huruf besar/kecil, dan angka.
- **Custom Security Headers**: Dilengkapi middleware proteksi Content Security Policy (CSP), X-Frame-Options, dan anti-sniffing.
- **Rate Limiting**: Pencegahan serangan *brute force* pada rute autentikasi/login.

---

## 🛠️ Tech Stack

- **Backend Framework**: [Laravel 12](https://laravel.com)
- **Frontend Adapter**: [Inertia.js v2](https://inertiajs.com)
- **Frontend Framework**: [Vue.js 3](https://vuejs.org) (Composition API & `<script setup>`)
- **UI Component Library**: [Vuetify 3](https://vuetifyjs.com) + [Material Design Icons (MDI)](https://pictogrammers.com/library/mdi/)
- **Rich Text Editor**: [TipTap Editor](https://tiptap.dev)
- **Alert & Notifikasi**: [SweetAlert2](https://sweetalert2.github.io)
- **Database**: MySQL / MariaDB / SQLite
- **Build Tool**: [Vite](https://vitejs.dev)

---

## 🚀 Panduan Instalasi Lokal (Development)

### Prasyarat
- PHP >= 8.2 (ekstensi aktif: `pdo`, `mbstring`, `openssl`, `gd` / `imagick`)
- Composer >= 2.x
- Node.js >= 18.x & NPM
- Web Server lokal (Laragon / XAMPP)

### Langkah-langkah

1. **Clone repository**:
   ```bash
   git clone https://github.com/knrdwahid/pcm-simo.git
   cd pcm-simo
   ```

2. **Install dependensi PHP**:
   ```bash
   composer install
   ```

3. **Install dependensi JavaScript**:
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**:
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Buka file `.env` dan sesuaikan konfigurasi database Anda.

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database & Seed Data Awal**:
   ```bash
   php artisan migrate --seed
   ```
   *(Opsional: Password default seeder dapat diatur melalui variabel `SEED_SUPERADMIN_PASSWORD`, `SEED_ADMIN_PASSWORD`, dan `SEED_TIM_PASSWORD` di `.env`)*

7. **Buat Symlink Storage**:
   ```bash
   php artisan storage:link
   ```

8. **Jalankan Development Server**:
   Buka dua jendela terminal terpisah:
   ```bash
   # Terminal 1: Backend Laravel
   php artisan serve

   # Terminal 2: Frontend Vite
   npm run dev
   ```
   Aplikasi dapat diakses melalui browser di `http://127.0.0.1:8000` atau domain virtual host lokal Anda.

---

## 📦 Panduan Build & Deployment (Hosting / cPanel / hPanel)

1. **Compile Asset Frontend**:
   Jalankan build sebelum upload ke server:
   ```bash
   npm run build
   ```
   Folder `public/build/` akan otomatis terisi bundle JavaScript & CSS yang telah diminifikasi.

2. **Optimasi Cache Laravel di Server**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Symlink Storage Publik**:
   Pastikan folder `public/storage` terhubung ke `storage/app/public`:
   ```bash
   php artisan storage:link
   ```

---

## 📄 Lisensi & Hak Cipta

Dikembangkan untuk **Pimpinan Cabang Muhammadiyah Simo**, Boyolali.  
Hak Cipta dilindungi. Kode sumber ini dapat digunakan dan dikembangkan untuk kemaslahatan dakwah persyarikatan.
