@php
// Definisi menu per role (global)
$menusByRole = [
'staf' => [
['key'=>'dashboard','label'=>'Dashboard','icon'=>'home','route'=>route('staf.dashboard')],
['key'=>'lkh','label'=>'Input LKH','icon'=>'file-edit','route'=>'#'],
['key'=>'map','label'=>'Peta Aktivitas','icon'=>'map-pin','route'=>'#'],
['key'=>'riwayat','label'=>'Riwayat','icon'=>'history','route'=>'#'],
['key'=>'log','label'=>'Log Aktivitas','icon'=>'clock','route'=>'#'],
],
'kepala-bagian' => [ /* …nanti tinggal diisi sesuai kebutuhan… */ ],
'kepala-dinas' => [ /* … */ ],
'admin' => [
['key'=>'dashboard','label'=>'Dashboard','icon'=>'home','route'=>'#'],
['key'=>'pengaturan','label'=>'Pengaturan Sistem','icon'=>'settings','route'=>'#'],
],
];
$menus = $menusByRole[$role] ?? $menusByRole['staf'];
@endphp

<aside id="sidebar" class="fixed lg:sticky inset-y-0 left-0 z-40 -translate-x-full lg:translate-x-0 transition-transform duration-200
          lg:top-5 w-[340px] text-white
          lg:h-[calc(100dvh-40px)]
          bg-[#1C7C54] rounded-[20px] overflow-hidden
          shadow-[0_12px_30px_rgba(0,0,0,0.18)] ring-1 ring-black/5 flex flex-col justify-between
          px-4 pt-6 pb-5">
    <div class="flex flex-col items-center text-center mb-8">
        <img src="{{ asset('img/logo-kab-mimika.png') }}" alt="Logo Kabupaten Mimika"
            class="h-[114px] w-[152px] object-contain mb-3" />
        <div class="leading-tight flex flex-col items-center gap-[6px]">
            <div class="font-semibold text-[20px]">Badan Pendapatan Daerah</div>
            <div class="text-[17px] font-normal ">Kabupaten Mimika</div>
        </div>
    </div>

    <!-- Menu Navigasi -->
    <nav class="flex flex-col gap-[5px]">
        {{-- Dashboard --}}
        <a href="{{ route('staf.dashboard') }}" class="flex text-[17px] items-center gap-3 rounded-xl px-4 py-3
                  {{ $active === 'dashboard'
                        ? 'bg-[#36B37E] text-white'
                        : 'text-white/90 hover:bg-[#36B37E]/70' }}">
            <img src="{{ asset('assets/icon/home.svg') }}" alt="Dashboard" class="h-5 w-5" />
            <span class="font-medium">Dashboard</span>
        </a>

        {{-- Input LKH --}}
        <a href="{{ route('staf.input-lkh') }}" class="flex text-[17px] items-center gap-3 px-4 py-3 rounded-xl transition
                  {{ $active === 'input-lkh'
                        ? 'bg-[#36B37E] text-white'
                        : 'text-white/90 hover:bg-[#36B37E]/70' }}">
            <img src="{{ asset('assets/icon/doc-laporan.svg') }}"
                class="h-5 w-5 {{ $active === 'input-lkh' ? 'filter invert brightness-0' : '' }}" alt="Input LKH">
            <span>Input LKH</span>
        </a>

        <a href="#" class="flex text-[17px] items-center gap-3 px-4 py-3 hover:bg-[#36B37E]/70 rounded-xl transition">
            <img src="{{ asset('assets/icon/doc-skp.svg') }}" alt="Input LKH" class="h-5 w-5" />
            <span>Input SKP</span>
        </a>

        <a href="#" class="flex text-[17px] items-center gap-3 px-4 py-3 hover:bg-[#36B37E]/70 rounded-xl transition">
            <img src="{{ asset('assets/icon/maps.svg') }}" alt="Peta Aktivitas" class="h-5 w-5" />
            <span>Peta Aktivitas</span>
        </a>

        <a href="#" class="flex text-[17px] items-center gap-3 px-4 py-3 hover:bg-[#36B37E]/70 rounded-xl transition">
            <img src="{{ asset('assets/icon/history.svg') }}" alt="Riwayat" class="h-5 w-5" />
            <span>Riwayat</span>
        </a>

        <a href="#" class="flex text-[17px] items-center gap-3 px-4 py-3 hover:bg-[#36B37E]/70 rounded-xl transition">
            <img src="{{ asset('assets/icon/log.svg') }}" alt="Log Aktivitas" class="h-5 w-5" />
            <span>Log Aktivitas</span>
        </a>
    </nav>

    <div class="mt-8 pt-8">
        <a href="#" class="flex text-[17px] items-center gap-3 px-4 py-3 hover:bg-[#36B37E]/70 rounded-xl transition">
            <img src="{{ asset('assets/icon/logout.svg') }}" alt="Riwayat" class="h-5 w-5" />
            <span>Logout</span>
        </a>
    </div>
</aside>