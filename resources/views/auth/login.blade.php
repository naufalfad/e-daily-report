<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login – E-Daily Report</title>

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Vite & Tailwind (v4: pastikan kamu sudah memuat CSS build/tailwind.css di bawah) --}}
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('build/tailwind.css') }}">

    <style>
    body {
        font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif
    }
    </style>
</head>

<body class="min-h-dvh bg-slate-100 text-slate-800">
    <!-- Grid 2 kolom: kiri (ilustrasi), kanan (form) -->
    <main class="grid min-h-dvh lg:grid-cols-[60%_40%]">
        <!-- KIRI: Panel hijau + hero + fitur -->
        <section class="relative hidden lg:block overflow-hidden">
            <!-- Background image -->
            <img src="{{ asset('img/bg-mimika.jpg') }}" class="absolute inset-0 h-full w-full object-cover"
                alt="Latar Mimika" />
            <!-- Overlay gradient hijau -->
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(13,92,62,0.88)_0%,rgba(24,140,96,0.90)_100%)]">
            </div>

            <div class="relative z-10 h-full pb-[12px] lg:pb-[15px]">
                <!-- Logo -->
                <div class="flex justify-center pt-10">
                    <img src="{{ asset('img/logo-kab-mimika.png') }}" alt="Kabupaten Mimika"
                        class="h-[150px] w-[224px] drop-shadow-[0_8px_24px_rgba(0,0,0,0.3)]">
                </div>

                <!-- Headline -->
                <div class="mx-auto mt-4 max-w-2xl px-10 text-center text-white">
                    <p class="text-[30px] font-normal">Selamat Datang di</p>
                    <h1 class="mt-1 text-[40px] font-semibold tracking-tight">
                        Aplikasi E-Daily Report</span>
                    </h1>
                    <p class="mt-3 text-[20px] font-normal">
                        Badan Pendapatan Daerah <br /> Kabupaten Mimika
                    </p>
                </div>

                <!-- Fitur (4 kartu) -->
                <div class="mx-auto mt-10 w-fit">
                    <div class="grid grid-cols-2 gap-[10px] justify-items-center">

                        <!-- Monitoring -->
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/80 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                            <div class="flex flex-col items-center text-center text-white">
                                <img src="{{ asset('assets/icon/monitoring-icon.svg') }}" alt="Monitoring Real-Time"
                                    class="mb-3 h-12 w-12">
                                <h3 class="text-[15px] font-semibold">Monitoring Real-Time</h3>
                                <p class="mt-1 text-sm/6 opacity-90">Pantau kinerja harian pegawai secara langsung</p>
                            </div>
                        </div>

                        <!-- Tracking -->
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/80 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                            <div class="flex flex-col items-center text-center text-white">
                                <img src="{{ asset('assets/icon/tracking-icon.svg') }}" alt="Tracking Lokasi"
                                    class="mb-3 h-12 w-12 text-white">
                                <h3 class="text-[15px] font-semibold">Tracking Lokasi</h3>
                                <p class="mt-1 text-sm/6 opacity-90">Lacak aktivitas lapangan dengan integrasi GPS</p>
                            </div>
                        </div>

                        <!-- Validasi -->
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/80 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                            <div class="flex flex-col items-center text-center text-white">
                                <img src="{{ asset('assets/icon/validasi-icon.svg') }}" alt="Validasi Digital"
                                    class="mb-3 h-12 w-12 text-white">
                                <h3 class="text-[15px] font-semibold">Validasi Digital</h3>
                                <p class="mt-1 text-sm/6 opacity-90">Proses persetujuan laporan cepat dan akurat</p>
                            </div>
                        </div>

                        <!-- Proteksi -->
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/80 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                            <div class="flex flex-col items-center text-center text-white">
                                <img src="{{ asset('assets/icon/proteksi-icon.svg') }}" alt="Data Terproteksi"
                                    class="mb-3 h-12 w-12 text-white">
                                <h3 class="text-[15px] font-semibold">Data Terproteksi</h3>
                                <p class="mt-1 text-sm/6 opacity-90">Keamanan data terjamin dengan enkripsi</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- KANAN: Form login -->
        <section class="flex items-center justify-center bg-slate-100 px-6 py-10">
            <div class="w-full max-w-md">
                <h2 class="text-3xl font-semibold text-[#1C7C54]">Login Aplikasi</h2>
                <p class="mt-2 text-slate-500">Silahkan masuk menggunakan akun anda</p>

                <form class="mt-8 space-y-5" action="#" method="GET">
                    <!-- Username -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" name="username" placeholder="Masukkan Username"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-[15px] placeholder-slate-400 outline-none focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20">
                    </div>

                    <!-- Password + toggle eye -->
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" placeholder="Masukkan Password"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-12 text-[15px] placeholder-slate-400 outline-none focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20">
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-2 my-auto inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-100"
                                aria-label="Tampilkan password" aria-pressed="false">
                                <!-- eye open (default) -->
                                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-width="1.8" d="M2 12s3.8-7 10-7 10 7 10 7-3.8 7-10 7S2 12 2 12Z" />
                                    <circle cx="12" cy="12" r="3.2" stroke-width="1.8" />
                                </svg>
                                <!-- eye off (kontras, tidak samar) -->
                                <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg"
                                    class="hidden h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor">
                                    <path stroke-width="1.8" d="M3 3l18 18" />
                                    <path stroke-width="1.8"
                                        d="M2.5 10s3.8 7 9.5 7c1.6 0 3.1-.3 4.4-.9M21.5 10S17.7 3 12 3c-.9 0-1.7.07-2.4.2" />
                                    <path stroke-width="1.8" d="M9.5 10.5a3.5 3.5 0 0 0 4 4" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol -->
                    <button type="button"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#1C7C54] px-4 py-3.5 text-[15px] font-medium text-white shadow-sm ring-1 ring-inset ring-[#1C7C54]/30 hover:brightness-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1C7C54]/40">
                        <!-- login icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-1 opacity-95" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor">
                            <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
                        </svg>
                        Masuk
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>

</html>