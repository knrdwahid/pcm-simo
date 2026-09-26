<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'PCM Simo') }}</title>
    <link rel="icon" type="image/png" href="/images/logo-muhammadiyah-warna.png">

    <!-- Google Fonts Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Fonts: Plus Jakarta Sans (Primary UI), Poppins (Headings/Brand), El Messiri (Arabic/Quote), Roboto Mono (Data) -->
    <link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&family=Roboto+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="antialiased font-poppins bg-[#F8FAF8] text-[#1E293B]">
    @inertia
</body>
</html>
