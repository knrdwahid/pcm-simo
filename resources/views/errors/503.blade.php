@extends('errors.layout')

@section('title', 'Pemberitahuan Resmi · Pemeliharaan Sistem')

@section('meta')
    <meta http-equiv="refresh" content="15">
@endsection

@section('content')
    <!-- Status Pill Badge -->
    <div style="display: flex; justify-content: center;">
        <span class="status-pill status-pill--green">
            <span class="pulse-beacon pulse-beacon--green"></span>
            Pemeliharaan &amp; Peningkatan Sistem
        </span>
    </div>

    <!-- Islamic Salam -->
    <div class="salam-box">
        <div class="salam-arabic">السَّلَامُ عَلَيْكُمْ وَرَحْمَةُ اللهِ وَبَرَكَاتُهُ</div>
        <div class="salam-latin">Assalamu'alaikum Warahmatullahi Wabarakatuh</div>
    </div>

    <!-- Main Title -->
    <h1 class="card-title">
        Portal Sedang Menjalani Pembaruan &amp; Peningkatan Layanan
    </h1>

    <!-- Announcement Body -->
    <p class="card-desc">
        Saat ini portal resmi <strong>Pimpinan Cabang Muhammadiyah Simo</strong> sedang menjalani pemeliharaan dan pembaruan sistem rutin guna meningkatkan kenyamanan, kecepatan akses, serta keamanan data persyarikatan.
    </p>

    <!-- Info Highlight Box -->
    <div class="info-highlight">
        <svg class="info-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="info-text">
            Proses pembaruan ini berlangsung singkat (umumnya <strong>kurang dari 1 menit</strong>). Sistem akan aktif kembali secara otomatis begitu pembaruan selesai.
        </div>
    </div>

    <!-- Countdown Progress Widget -->
    <div class="countdown-box">
        <div class="countdown-header">
            <span>Memeriksa ketersediaan portal:</span>
            <span class="countdown-value"><span id="countdown-val">15</span> detik</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill" id="progress-fill" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Actions -->
    <div class="action-group">
        <button type="button" class="btn btn-primary" id="btn-reload" onclick="handleManualReload()">
            <svg id="reload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span id="reload-text">Muat Ulang Halaman</span>
        </button>

        <a href="https://wa.me/6281234567890?text=Assalamu%27alaikum%20Admin%20PCM%20Simo" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span>Hubungi Sekretariat</span>
        </a>
    </div>
@endsection

@section('scripts')
<script>
    (function() {
        var totalSeconds = 15;
        var remainingSeconds = totalSeconds;
        var countdownEl = document.getElementById('countdown-val');
        var progressEl = document.getElementById('progress-fill');
        var btnReload = document.getElementById('btn-reload');
        var reloadIcon = document.getElementById('reload-icon');
        var reloadText = document.getElementById('reload-text');

        var timer = setInterval(function() {
            remainingSeconds--;
            if (countdownEl) {
                countdownEl.textContent = remainingSeconds;
            }
            if (progressEl) {
                var pct = Math.max(0, (remainingSeconds / totalSeconds) * 100);
                progressEl.style.width = pct + '%';
            }

            if (remainingSeconds <= 0) {
                clearInterval(timer);
                triggerReload();
            }
        }, 1000);

        function triggerReload() {
            if (reloadIcon) {
                reloadIcon.classList.add('is-spinning');
            }
            if (reloadText) {
                reloadText.textContent = 'Memeriksa Sistem...';
            }
            if (btnReload) {
                btnReload.disabled = true;
                btnReload.style.opacity = '0.75';
            }
            setTimeout(function() {
                window.location.reload();
            }, 300);
        }

        window.handleManualReload = function() {
            clearInterval(timer);
            triggerReload();
        };
    })();
</script>
@endsection
