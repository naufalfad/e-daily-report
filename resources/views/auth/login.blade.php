<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- [PERBAIKAN 1] Menambahkan CSRF Token (Wajib untuk Ajax) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login – E-Daily Report</title>

    {{-- Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    {{-- Vite & Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
    body {
        font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif
    }
    </style>
</head>

<body class="min-h-dvh bg-slate-100 text-slate-800">
    <main class="grid min-h-dvh lg:grid-cols-[60%_40%]">

        {{-- BAGIAN KIRI (GAMBAR & FITUR) --}}
        <section class="relative hidden lg:block overflow-hidden">
            <img src="{{ asset('img/bapenda-gpt.jpg') }}" class="absolute inset-0 h-full w-full object-cover"
                alt="Latar Mimika" />
            <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(13,92,62,0.0)_0%,rgba(24,140,96,0.0)_100%)]">
            </div>

            <div class="relative z-10 h-full pb-[12px] lg:pb-[15px]">
                <div class="flex justify-center pt-10">
                    <img src="{{ asset('img/logo-kab-mimika.png') }}" alt="Kabupaten Mimika"
                        class="h-[150px] w-[224px] drop-shadow-[0_8px_24px_rgba(0,0,0,0.3)]">
                </div>

                <div class="mx-auto mt-4 max-w-2xl px-10 text-center text-white">
                    <p class="text-[30px] font-normal">Selamat Datang di</p>
                    <h1 class="mt-1 text-[40px] font-semibold tracking-tight">
                        Aplikasi E-Daily Report
                    </h1>
                    <p class="mt-3 text-[20px] font-normal">
                        Badan Pendapatan Daerah <br /> Kabupaten Mimika
                    </p>
                </div>

                {{-- Feature Cards --}}
                <div class="mx-auto mt-10 w-fit">
                    <div class="grid grid-cols-2 gap-[10px] justify-items-center">
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/40 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                            <div class="flex flex-col items-center text-center text-white">
                                <img src="{{ asset('assets/icon/monitoring-icon.svg') }}" alt="Monitoring Real-Time"
                                    class="mb-3 h-12 w-12">
                                <h3 class="text-[15px] font-semibold">Monitoring Real-Time</h3>
                                <p class="mt-1 text-sm/6 opacity-90">Pantau kinerja harian pegawai secara langsung</p>
                            </div>
                        </div>
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/40 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                            <div class="flex flex-col items-center text-center text-white">
                                <img src="{{ asset('assets/icon/tracking-icon.svg') }}" alt="Tracking Lokasi"
                                    class="mb-3 h-12 w-12 text-white">
                                <h3 class="text-[15px] font-semibold">Tracking Lokasi</h3>
                                <p class="mt-1 text-sm/6 opacity-90">Lacak aktivitas lapangan dengan integrasi GPS</p>
                            </div>
                        </div>
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/40 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
                            <div class="flex flex-col items-center text-center text-white">
                                <img src="{{ asset('assets/icon/validasi-icon.svg') }}" alt="Validasi Digital"
                                    class="mb-3 h-12 w-12 text-white">
                                <h3 class="text-[15px] font-semibold">Validasi Digital</h3>
                                <p class="mt-1 text-sm/6 opacity-90">Proses persetujuan laporan cepat dan akurat</p>
                            </div>
                        </div>
                        <div
                            class="w-[304px] h-[167px] border border-[#CBD6E0]/50 rounded-2xl bg-[#1C7C54]/40 flex flex-col items-center justify-center text-center text-white shadow-[0_4px_10px_rgba(0,0,0,0.15)]">
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

        {{-- BAGIAN KANAN (FORM LOGIN) --}}
        <section class="flex items-center justify-center b-slate-100 px-6 py-10">
            <div class="w-full max-w-md">
                <h2 class="text-3xl font-semibold text-[#1C7C54]">Login Aplikasi</h2>
                <p class="mt-2 text-slate-500">Silahkan masuk menggunakan akun anda</p>

                {{-- [PERBAIKAN 2] ID Form disesuaikan menjadi 'login-form' --}}
                <form id="login-form" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Username (NIP / Email)</label>
                        <input type="text" name="username" id="username" placeholder="Masukkan NIP atau Email" required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-[15px] placeholder-slate-400 outline-none focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 transition">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" placeholder="Masukkan Password"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-12 text-[15px] placeholder-slate-400 outline-none focus:border-[#1C7C54] focus:ring-2 focus:ring-[#1C7C54]/20 transition">

                            {{-- Toggle Password Button --}}
                            <button type="button" id="togglePassword"
                                class="absolute inset-y-0 right-2 my-auto inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-slate-100 transition"
                                aria-label="Tampilkan password" aria-pressed="false">
                                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-width="1.8" d="M2 12s3.8-7 10-7 10 7 10 7-3.8 7-10 7S2 12 2 12Z" />
                                    <circle cx="12" cy="12" r="3.2" stroke-width="1.8" />
                                </svg>
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

                    <button type="submit" id="btn-submit"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#1C7C54] px-4 py-3.5 text-[15px] font-medium text-white shadow-sm ring-1 ring-inset ring-[#1C7C54]/30 hover:brightness-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1C7C54]/40 disabled:opacity-70 disabled:cursor-not-allowed transition">

                        {{-- Loading Icon --}}
                        <svg id="btn-loader" class="hidden animate-spin h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>

                        {{-- Button Text --}}
                        <span id="btn-text" class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-1 opacity-95" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor">
                                <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" />
                            </svg>
                            Masuk
                        </span>
                    </button>
                </form>
            </div>
        </section>
    </main>

    {{-- Script untuk Toggle Password (Opsional jika tidak ada di login.js) --}}
    <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    });
    </script>
</body>

</html>