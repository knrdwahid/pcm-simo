@extends('errors.layout')

@section('title', '419 · Sesi Telah Kedaluwarsa')

@section('content')
    <!-- Status Pill Badge -->
    <div style="display: flex; justify-content: center;">
        <span class="status-pill status-pill--amber">
            <span class="pulse-beacon pulse-beacon--amber"></span>
            Keamanan Sesi &bull; Kode 419
        </span>
    </div>

    <!-- Islamic Salam -->
    <div class="salam-box">
        <div class="salam-arabic">السَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللهِ وَبَرَكَاتُهُ</div>
        <div class="salam-latin">Assalamu'alaikum Warahmatullahi Wabarakatuh</div>
    </div>

    <!-- Main Title -->
    <h1 class="card-title">
        Sesi Halaman Telah Kedaluwarsa
    </h1>

    <!-- Announcement Body -->
    <p class="card-desc">
        Demi menjaga keamanan transaksi data pada portal <strong>Pimpinan Cabang Muhammadiyah Simo</strong>, batas waktu sesi pengisian formulir Anda telah berakhir.
    </p>

    <!-- Info Highlight Box -->
    <div class="info-highlight">
        <svg class="info-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="info-text">
            Silakan muat ulang halaman ini untuk mendapatkan token keamanan baru, kemudian coba kirimkan formulir Anda kembali.
        </div>
    </div>

    <!-- Actions -->
    <div class="action-group">
        <button type="button" onclick="window.location.reload()" class="btn btn-primary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>Muat Ulang Halaman</span>
        </button>

        <a href="/" class="btn btn-secondary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
    </div>
@endsection
