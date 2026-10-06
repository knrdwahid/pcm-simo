@extends('errors.layout')

@section('title', '500 · Kendala Teknis Sistem')

@section('content')
    <!-- Status Pill Badge -->
    <div style="display: flex; justify-content: center;">
        <span class="status-pill status-pill--rose">
            <span class="pulse-beacon pulse-beacon--rose"></span>
            Pemberitahuan Sistem &bull; Kode 500
        </span>
    </div>

    <!-- Islamic Salam -->
    <div class="salam-box">
        <div class="salam-arabic">السَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللهِ وَبَرَكَاتُهُ</div>
        <div class="salam-latin">Assalamu'alaikum Warahmatullahi Wabarakatuh</div>
    </div>

    <!-- Main Title -->
    <h1 class="card-title">
        Terjadi Kendala Teknis Sementara pada Server
    </h1>

    <!-- Announcement Body -->
    <p class="card-desc">
        Mohon maaf atas ketidaknyamanan ini. Server portal <strong>Pimpinan Cabang Muhammadiyah Simo</strong> sedang mengalami kendala sementara saat memproses permintaan Anda.
    </p>

    <!-- Info Highlight Box -->
    <div class="info-highlight">
        <svg class="info-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="info-text">
            Tim teknis dan pengelola media PCM Simo secara otomatis menerima laporan log kejadian ini dan sedang melakukan perbaikan. Silakan muat ulang halaman beberapa saat lagi.
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

        <a href="https://wa.me/6281234567890?text=Assalamu%27alaikum%20Admin%20PCM%20Simo,%20terjadi%20kendala%20sistem" target="_blank" rel="noopener noreferrer" class="btn btn-outline-green">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span>Laporkan ke Sekretariat</span>
        </a>
    </div>
@endsection
