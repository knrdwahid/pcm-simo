@extends('errors.layout')

@section('title', '403 · Akses Dibatasi')

@section('content')
    <!-- Status Pill Badge -->
    <div style="display: flex; justify-content: center;">
        <span class="status-pill status-pill--slate">
            <span class="pulse-beacon pulse-beacon--amber"></span>
            Keamanan Sistem &bull; Kode 403
        </span>
    </div>

    <!-- Islamic Salam -->
    <div class="salam-box">
        <div class="salam-arabic">السَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللهِ وَبَرَكَاتُهُ</div>
        <div class="salam-latin">Assalamu'alaikum Warahmatullahi Wabarakatuh</div>
    </div>

    <!-- Main Title -->
    <h1 class="card-title">
        Akses Halaman atau Fitur Ini Dibatasi
    </h1>

    <!-- Announcement Body -->
    <p class="card-desc">
        Halaman atau tindakan yang Anda akses memerlukan izin atau hak akses otorisasi khusus dari pengurus/administrator <strong>Pimpinan Cabang Muhammadiyah Simo</strong>.
    </p>

    <!-- Info Highlight Box -->
    <div class="info-highlight">
        <svg class="info-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <div class="info-text">
            Bila Anda merupakan petugas atau pengurus majelis/lembaga yang berwenang, silakan masuk melalui form otentikasi resmi.
        </div>
    </div>

    <!-- Actions -->
    <div class="action-group">
        <a href="/login" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>Masuk ke Panel Petugas</span>
        </a>

        <a href="/" class="btn btn-secondary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
@endsection
