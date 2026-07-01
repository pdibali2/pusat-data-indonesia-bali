<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Bantuan</title>
    <meta name="description" content="Bantuan dan panduan penggunaan fitur Pusat Data Indonesia Bali untuk pengguna baru dan lama."/>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="bg-slate-50 text-gray-900 antialiased">

    @include('pages.landing.components.navbar')

    <main class="pb-24 min-h-screen">

        {{-- HERO --}}
        <div class="bg-stikom py-20 pb-16 relative overflow-hidden border-b-4 border-stikom-accent">
            <div class="absolute inset-0 opacity-[.06]" aria-hidden="true"
                 style="background-image:repeating-linear-gradient(0deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px),repeating-linear-gradient(90deg,rgba(255,255,255,.4) 0,rgba(255,255,255,.4) 1px,transparent 1px,transparent 40px)">
            </div>
            <div class="absolute inset-0 opacity-[.07]"
                 style="background-image:radial-gradient(circle,#3d6db1 1px,transparent 1px);background-size:24px 24px"
                 aria-hidden="true"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-block text-stikom-accent text-xs font-bold tracking-[0.2em] uppercase mb-4">
                    Pusat Bantuan
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black font-poppins text-white mb-4 leading-tight">
                    Panduan Penggunaan Platform
                </h1>
                <p class="text-white/60 text-base max-w-2xl mx-auto">
                    Petunjuk untuk menggunakan layanan Pusat Data Indonesia Bali.
                </p>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-12" x-data="helpAccordion()">

            <div class="lg:grid lg:grid-cols-1 lg:gap-10">

                {{-- ACCORDION LIST --}}
                <div class="space-y-3">
                    @foreach ($topics as $i => $topic)
                        <div id="topik-{{ $i }}" class="bg-white border border-gray-200 scroll-mt-24">
                            <button type="button"
                                    @click="toggle({{ $i }})"
                                    :aria-expanded="open === {{ $i }}"
                                    class="w-full flex items-center gap-4 text-left px-5 py-4 group">

                                <span class="flex-shrink-0 w-10 h-10 flex items-center justify-center border-2 transition-colors duration-150"
                                      :class="open === {{ $i }} ? 'bg-stikom border-stikom text-white' : 'border-gray-200 text-stikom-blue'">
                                    {!! $topic['icon'] !!}
                                </span>

                                <span class="flex-1">
                                    <span class="block text-[11px] font-bold tracking-widest uppercase text-gray-400">
                                        Topik {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <span class="block text-base font-bold font-poppins text-stikom leading-snug">
                                        {{ $topic['title'] }}
                                    </span>
                                </span>

                                <svg class="flex-shrink-0 w-5 h-5 text-gray-400 transition-transform duration-200"
                                     :class="open === {{ $i }} ? 'rotate-45 text-stikom-accent' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>

                            <div x-show="open === {{ $i }}"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 x-cloak>
                                <div class="border-t border-gray-100 px-5 pt-4 pb-6 sm:pl-[76px]">
                                    <p class="text-gray-600 leading-relaxed mb-4">{{ $topic['intro'] }}</p>

                                    <ol class="space-y-3">
                                        @foreach ($topic['steps'] as $j => $step)
                                            <li class="flex gap-3">
                                                <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center bg-stikom-accent/10 text-stikom text-xs font-bold mt-0.5">
                                                    {{ $j + 1 }}
                                                </span>
                                                <span class="text-gray-600 leading-relaxed">{!! $step !!}</span>
                                            </li>
                                        @endforeach
                                    </ol>

                                    @if (!empty($topic['tip']))
                                        <div class="mt-4 flex gap-2 border-l-2 border-stikom-accent bg-stikom-accent/5 px-4 py-3">
                                            <svg class="w-4 h-4 flex-shrink-0 text-stikom-accent mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                                            <p class="text-xs text-stikom-blue leading-relaxed"><span class="font-bold">Tips: </span>{{ $topic['tip'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CONTACT / FALLBACK --}}
            {{-- <div class="mt-14 border border-gray-200 bg-white px-6 py-10 sm:px-10 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div>
                    <p class="text-lg font-bold font-poppins text-stikom mb-1">Belum menemukan yang Anda cari?</p>
                    <p class="text-sm text-gray-500">Kirimkan pertanyaan Anda dan tim kami akan segera membantu.</p>
                </div>
                <a href="mailto:support@pdib.baliprov.go.id"
                   class="flex-shrink-0 inline-flex items-center gap-2 bg-stikom-accent text-stikom px-6 py-3 font-bold text-sm hover:bg-stikom-accent/90 transition-colors">
                    Hubungi Tim Support
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div> --}}
        </div>

    </main>

    @include('pages.landing.components.footer')

    <script>
        function helpAccordion() {
            return {
                open: 0,
                toggle(index) {
                    this.open = this.open === index ? null : index;
                }
            }
        }
    </script>

</body>
</html>