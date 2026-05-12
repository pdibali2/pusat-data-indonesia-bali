<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    {{-- SEO --}}
    <title>Pusat Data Indonesia Bali</title>
    <meta name="description" content="Pusat Data Indonesia Bali adalah layanan penyedia data terpusat untuk memudahkan pengguna menemukan dan mengakses data sesuai kebutuhan."/>
    <meta name="keywords" content="pusat data, bali, data indonesia, statistik bali, indikator, metadata"/>
    <meta name="author" content="Pusat Data Indonesia - Bali"/>
    <link rel="canonical" href="{{ url('/') }}"/>

    {{-- Open Graph --}}
    <meta property="og:title" content="Pusat Data Indonesia - Bali"/>
    <meta property="og:description" content="Layanan penyedia data terpusat untuk memudahkan pengguna menemukan dan mengakses data sesuai kebutuhan."/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="{{ url('/') }}"/>
    <meta property="og:locale" content="id_ID"/>

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:title" content="Pusat Data Indonesia - Bali"/>
    <meta name="twitter:description" content="Platform data terpusat Provinsi Bali."/>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>

    {{-- ✅ Semua font, Tailwind, dan Alpine via Vite pipeline.
         Tidak ada lagi CDN atau inline <style> untuk font/tailwind. --}}
    @vite(['resources/css/app.css', 'resources/js/app.ts'])

    {{-- CSRF for AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Skip to content (accessibility) --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[999]
              focus:px-4 focus:py-2 focus:bg-stikom-accent focus:text-white focus:rounded-lg
              focus:text-sm focus:font-semibold">
        Lewati ke konten utama
    </a>

    {{-- Navbar --}}
    @include('pages.components.navbar')

    {{-- Main content --}}
    <main id="main-content">
        @include('pages.components.hero')
        @include('pages.components.about')
        @include('pages.components.dataset-preview', ['produkUnggulan' => $produkUnggulan])
        @include('pages.components.features')
        @include('pages.components.statistics')
        @include('pages.components.subscription')
    </main>

    {{-- Footer --}}
    @include('pages.components.footer')

    {{-- Back to Top button --}}
    <button
        id="back-to-top"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-50 w-12 h-12 rounded-2xl bg-stikom-accent text-white
               shadow-xl shadow-stikom-accent/30 flex items-center justify-center
               opacity-0 translate-y-4 pointer-events-none
               transition-all duration-300 hover:bg-stikom-accent hover:scale-110
               focus:outline-none focus:ring-2 focus:ring-stikom-accent focus:ring-offset-2"
        aria-label="Kembali ke atas"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    {{-- Global JS --}}
    <script>
    // ─── Back to Top ────────────────────────────────────────────
    (function () {
        const btn = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            const past = window.scrollY > 300;
            btn.classList.toggle('opacity-0',           !past);
            btn.classList.toggle('translate-y-4',       !past);
            btn.classList.toggle('pointer-events-none', !past);
            btn.classList.toggle('opacity-100',         past);
            btn.classList.toggle('translate-y-0',       past);
        }, { passive: true });
    })();

    // ─── Scroll Reveal ───────────────────────────────────────────
    // .fade-up / .reveal classes are now defined in app.css
    (function () {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('#tentang, #fitur, #langganan').forEach(section => {
            section.querySelectorAll('.fade-up').forEach((el, i) => {
                el.classList.remove('fade-up');
                el.classList.add('reveal');
                el.style.transitionDelay = `${i * 0.08}s`;
                observer.observe(el);
            });
        });
    })();
    </script>

</body>
</html>