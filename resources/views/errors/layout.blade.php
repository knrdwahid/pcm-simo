<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pemberitahuan Resmi') · PCM Simo</title>
    <link rel="icon" type="image/png" href="/images/logo-muhammadiyah-warna.png">
    @yield('meta')

    <!-- Typography (Google Fonts with System Fallbacks) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

    <style>
        /* Standalone Resilient Styling - No External Vite/CSS Dependency */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --primary: #006837;
            --primary-dark: #004d28;
            --primary-light: #008746;
            --primary-50: #f0fdf4;
            --primary-100: #dcfce7;
            --gold: #d97706;
            --gold-light: #f59e0b;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --white: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f7faf8;
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(0, 104, 55, 0.08) 0%, transparent 55%),
                radial-gradient(circle at 100% 100%, rgba(217, 119, 6, 0.04) 0%, transparent 40%),
                linear-gradient(180deg, #f0fdf4 0%, #f7faf8 350px);
            color: var(--slate-800);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Top Bar */
        .top-strip {
            width: 100%;
            max-width: 680px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 16px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(0, 104, 55, 0.12);
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            color: var(--primary);
            letter-spacing: 0.05em;
            text-transform: uppercase;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .top-strip-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-strip-dot {
            width: 7px;
            height: 7px;
            background: var(--primary);
            border-radius: 50%;
        }

        .top-strip-tag {
            color: var(--slate-500);
            font-size: 11px;
            font-weight: 500;
        }

        /* Main Announcement Card */
        .announcement-card {
            width: 100%;
            max-width: 680px;
            background: var(--white);
            border-radius: 28px;
            border: 1px solid rgba(0, 104, 55, 0.14);
            box-shadow: 
                0 20px 40px -15px rgba(0, 104, 55, 0.09),
                0 0 1px 1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            position: relative;
            transition: transform 0.2s ease;
        }

        /* Decorative Header Gradient */
        .card-header-bar {
            height: 6px;
            background: linear-gradient(90deg, #006837 0%, #10b981 60%, #d97706 100%);
        }

        .card-content {
            padding: 40px 36px 36px;
            text-align: center;
        }

        @media (max-width: 640px) {
            .card-content {
                padding: 28px 20px 24px;
            }
            .top-strip {
                display: none;
            }
        }

        /* Organization Identity Lockup */
        .org-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .org-logo-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .org-logo-img {
            height: 58px;
            width: auto;
            max-width: 220px;
            object-fit: contain;
        }

        .org-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .org-subtitle {
            font-size: 12px;
            font-weight: 500;
            color: var(--slate-500);
            letter-spacing: 0.02em;
            margin-top: 2px;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 20px auto 28px;
            max-width: 380px;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(0, 104, 55, 0.18), transparent);
        }

        .divider-icon {
            color: var(--gold);
            font-size: 14px;
            line-height: 1;
        }

        /* Status Pill Badge */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .status-pill--green {
            background-color: var(--primary-100);
            color: #14532d;
            border: 1px solid #86efac;
        }

        .status-pill--amber {
            background-color: #fef3c7;
            color: #78350f;
            border: 1px solid #fcd34d;
        }

        .status-pill--rose {
            background-color: #ffe4e6;
            color: #881337;
            border: 1px solid #fecdd3;
        }

        .status-pill--slate {
            background-color: #f1f5f9;
            color: #1e293b;
            border: 1px solid #cbd5e1;
        }

        /* Beacon Pulse Animation */
        .pulse-beacon {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            position: relative;
        }

        .pulse-beacon--green {
            background-color: #16a34a;
            box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.6);
            animation: pulse-ring-green 2s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        .pulse-beacon--amber {
            background-color: #d97706;
            box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.6);
            animation: pulse-ring-amber 2s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        .pulse-beacon--rose {
            background-color: #e11d48;
            box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.6);
            animation: pulse-ring-rose 2s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        @keyframes pulse-ring-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(22, 163, 74, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
        }

        @keyframes pulse-ring-amber {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(217, 119, 6, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(217, 119, 6, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(217, 119, 6, 0); }
        }

        @keyframes pulse-ring-rose {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 7px rgba(225, 29, 72, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); }
        }

        /* Islamic Salam */
        .salam-box {
            margin-bottom: 20px;
        }

        .salam-arabic {
            font-family: 'Amiri', 'Traditional Arabic', serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            direction: rtl;
            margin-bottom: 4px;
            letter-spacing: 0;
            line-height: 1.4;
        }

        .salam-latin {
            font-size: 13px;
            font-style: italic;
            color: var(--slate-600);
            font-weight: 500;
        }

        /* Error Code / Headline */
        .error-code {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: var(--gold);
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .card-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1.35;
            margin-bottom: 14px;
        }

        @media (max-width: 640px) {
            .card-title {
                font-size: 19px;
            }
            .salam-arabic {
                font-size: 21px;
            }
        }

        .card-desc {
            font-size: 14.5px;
            color: var(--slate-600);
            line-height: 1.65;
            max-width: 540px;
            margin: 0 auto 26px;
        }

        /* Informational Highlight Card */
        .info-highlight {
            background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid rgba(0, 104, 55, 0.16);
            border-radius: 16px;
            padding: 16px 20px;
            margin: 0 auto 28px;
            max-width: 540px;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .info-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
            color: var(--primary);
            margin-top: 1px;
        }

        .info-text {
            font-size: 13px;
            color: #14532d;
            line-height: 1.55;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            outline: none;
            border: 1px solid transparent;
            font-family: inherit;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 4px 14px rgba(0, 104, 55, 0.25);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 104, 55, 0.32);
        }

        .btn-secondary {
            background-color: var(--white);
            color: var(--slate-700);
            border-color: var(--slate-200);
        }

        .btn-secondary:hover {
            background-color: var(--slate-50);
            border-color: var(--slate-300);
            color: var(--slate-900);
            transform: translateY(-1px);
        }

        .btn-outline-green {
            background-color: rgba(0, 104, 55, 0.04);
            color: var(--primary);
            border-color: rgba(0, 104, 55, 0.2);
        }

        .btn-outline-green:hover {
            background-color: rgba(0, 104, 55, 0.08);
            border-color: var(--primary);
            color: var(--primary-dark);
        }

        .btn svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        /* Countdown Widget (for 503 Maintenance) */
        .countdown-box {
            background: #ffffff;
            border: 1px solid rgba(0, 104, 55, 0.15);
            border-radius: 16px;
            padding: 18px 20px;
            margin: 0 auto 24px;
            max-width: 520px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
        }

        .countdown-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            color: var(--slate-700);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .countdown-value {
            color: var(--primary);
            font-weight: 800;
            font-size: 15px;
        }

        .progress-track {
            width: 100%;
            height: 6px;
            background: var(--slate-100);
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #006837, #10b981);
            border-radius: 999px;
            transition: width 1s linear;
        }

        /* Card Footer Notes */
        .card-secretariat {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--slate-100);
            font-size: 12px;
            color: var(--slate-500);
            line-height: 1.6;
        }

        .card-secretariat strong {
            color: var(--slate-700);
        }

        /* Main Page Footer */
        .page-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 12px;
            color: var(--slate-500);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .page-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .page-footer a:hover {
            text-decoration: underline;
        }

        /* Subtle Spinning utility */
        .is-spinning {
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Official Top Strip -->
    <header class="top-strip">
        <div class="top-strip-left">
            <span class="top-strip-dot"></span>
            <span>Portal Resmi Persyarikatan</span>
        </div>
        <div class="top-strip-tag">
            Pimpinan Cabang Muhammadiyah Simo
        </div>
    </header>

    <!-- Main Card -->
    <main class="announcement-card">
        <div class="card-header-bar"></div>
        <div class="card-content">

            <!-- Organization Brand Lockup -->
            <div class="org-brand">
                <div class="org-logo-wrap">
                    <img 
                        src="/images/logo-pcmsimo-warna.png" 
                        alt="Logo PCM Simo" 
                        class="org-logo-img"
                        onerror="this.onerror=null; this.src='/images/logo-muhammadiyah-warna.png';"
                    >
                </div>
                <div class="org-title">Pimpinan Cabang Muhammadiyah Simo</div>
                <div class="org-subtitle">Daerah Boyolali &bull; Jawa Tengah</div>
            </div>

            <!-- Decorative Divider -->
            <div class="divider">
                <span class="divider-line"></span>
                <span class="divider-icon">&#10022;</span>
                <span class="divider-line"></span>
            </div>

            <!-- Child Content (Error Status, Salam, Message, Buttons) -->
            @yield('content')

            <!-- Secretariat Footer -->
            <div class="card-secretariat">
                <strong>Gedung Dakwah Muhammadiyah Simo</strong><br>
                Jl. Raya Simo - Bangak Km. 1, Simo, Boyolali 57377 &bull; Telp. (0276) 3294404
            </div>

        </div>
    </main>

    <!-- Page Footer -->
    <footer class="page-footer">
        <div>&copy; {{ date('Y') }} <strong>Pimpinan Cabang Muhammadiyah Simo</strong>. Seluruh hak cipta dilindungi.</div>
        <div>Menegakkan & Menjunjung Tinggi Agama Islam demi Terwujudnya Masyarakat Islam yang Sebenar-benarnya.</div>
    </footer>

    @yield('scripts')
</body>
</html>
