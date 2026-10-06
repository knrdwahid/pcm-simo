@extends('errors.layout')

@section('title', '404 · Halaman Tidak Ditemukan')

@section('content')
    <!-- Status Pill Badge -->
    <div style="display: flex; justify-content: center;">
        <span class="status-pill status-pill--amber">
            <span class="pulse-beacon pulse-beacon--amber"></span>
            Pemberitahuan Navigasi &bull; Kode 404
        </span>
    </div>

    <!-- Islamic Salam -->
    <div class="salam-box">
        <div class="salam-arabic">السَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللهِ وَبَرَكَاتُهُ</div>
        <div class="salam-latin">Assalamu'alaikum Warahmatullahi Wabarakatuh</div>
    </div>

    <!-- Main Title -->
    <h1 class="card-title">
        Halaman yang Anda Tuju Tidak Ditemukan
    </h1>

    <!-- Announcement Body -->
    <p class="card-desc">
        Mohon maaf, tautan atau halaman portal yang Anda tuju saat ini belum tersedia, telah dipindahkan ke alamat baru, atau alamat web yang dimasukkan kurang tepat.
    </p>

    <!-- Info Highlight Box -->
    <div class="info-highlight">
        <svg class="info-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="info-text">
            Anda dapat kembali ke halaman utama untuk menelusuri berita, agenda persyarikatan, profil pimpinan, maupun profil Amal Usaha Muhammadiyah (AUM) Simo.
        </div>
    </div>

    <!-- Actions -->
    <div class="action-group">
        <a href="/" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Kembali ke Beranda Utama</span>
        </a>

        <button type="button" onclick="history.back()" class="btn btn-secondary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Halaman Sebelumnya</span>
        </button>

        <a href="/berita" class="btn btn-outline-green">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <span>Kabar &amp; Berita Simo</span>
        </a>
    </div>
@endsection
