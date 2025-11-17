@php
// Definisi menu per role (global)
$menusByRole = [
    'staf' => [
        ['key'=>'dashboard','label'=>'Dashboard','icon'=>'home','route'=>route('staf.dashboard')],
        ['key'=>'lkh','label'=>'Input LKH','icon'=>'file-edit','route'=>route('staf.input-lkh')], 
        ['key'=>'skp','label'=>'Input SKP','icon'=>'doc-skp','route'=>route('staf.input-skp')],
        ['key'=>'map','label'=>'Peta Aktivitas','icon'=>'map-pin','route'=>route('staf.peta-aktivitas')],
        ['key'=>'riwayat','label'=>'Riwayat','icon'=>'history','route'=>route('staf.riwayat-lkh')],
        ['key'=>'log','label'=>'Log Aktivitas','icon'=>'clock','route'=>route('staf.log-aktivitas')],
    ],
    // Role lain disembunyikan untuk ringkasnya...
    'kepala-bagian' => [ /* …nanti tinggal diisi sesuai kebutuhan… */ ],
    'kepala-dinas' => [ /* … */ ],
    'admin' => [
        ['key'=>'dashboard','label'=>'Dashboard','icon'=>'home','route'=>'#'],
        ['key'=>'pengaturan','label'=>'Pengaturan Sistem','icon'=>'settings','route'=>'#'],
    ],
];
// Gunakan $active dan $role dari @extends()
$activeMenu = $active ?? 'dashboard';
$menus = $menusByRole[$role] ?? $menusByRole['staf']; 
@endphp

<aside id="sidebar" class="fixed lg:sticky inset-y-0 left-0 z-40 -translate-x-full lg:translate-x-0 transition-transform duration-200
              lg:top-5 w-[340px] text-white
              lg:h-[calc(100dvh-40px)]
              bg-[#1C7C54] rounded-[20px] overflow-hidden
              shadow-[0_12px_30px_rgba(0,0,0,0.18)] ring-1 ring-black/5
              px-4 pt-6 pb-5
              flex flex-col">

    {{-- Header Sidebar --}}
    <div class="flex flex-col items-center text-center mb-8 shrink-0">
        <img src="{{ asset('img/logo-kab-mimika.png') }}" alt="Logo Kabupaten Mimika"
            class="h-[114px] w-[152px] object-contain mb-3" />
        <div class="leading-tight flex flex-col items-center gap-[6px]">
            <div class="font-semibold text-[20px]">Badan Pendapatan Daerah</div>
            <div class="text-[17px] font-normal">Kabupaten Mimika</div>
        </div>
    </div>

    <nav class="flex flex-col gap-[5px]">
        @foreach ($menus as $menu)
        <a href="{{ $menu['route'] }}" class="flex text-[17px] items-center gap-3 px-4 py-3 rounded-xl transition
                      {{ $activeMenu === $menu['key']
                            ? 'bg-[#36B37E] text-white'
                            : 'text-white/90 hover:bg-[#36B37E]/70' }}">
                
                @php 
                    $iconMap = [
                        'home' => 'home.svg', 'file-edit' => 'doc-laporan.svg',
                        'doc-skp' => 'doc-skp.svg', 'map-pin' => 'maps.svg',
                        'history' => 'history.svg', 'clock' => 'log.svg', 'settings' => 'settings.svg'
                    ];
                    $iconFile = $iconMap[$menu['icon']] ?? 'home.svg';
                @endphp

            @php
            $iconMap = [
            'home' => 'home.svg',
            'file-edit' => 'doc-laporan.svg',
            'doc-skp' => 'doc-skp.svg',
            'map-pin' => 'maps.svg',
            'history' => 'history.svg',
            'clock' => 'log.svg',
            'settings' => 'settings.svg',
            'announcement' => 'pengumuman.svg',
            'validation' => 'validation.svg',
            'skoring' => 'skoring.svg',
            ];
            $iconFile = $iconMap[$menu['icon']] ?? 'home.svg';
            @endphp

            <img src="{{ asset('assets/icon/' . $iconFile) }}" alt="{{ $menu['label'] }}"
                class="h-5 w-5 {{ $activeMenu === $menu['key'] ? 'filter invert brightness-0' : '' }}" />
            <span>{{ $menu['label'] }}</span>
        </a>
        @endforeach
    </nav>

    {{-- Footer Sidebar --}}
    <div class="mt-8 pt-8">
        {{-- [UBAH] Tambahkan ID "btn-logout" di sini --}}
        <a href="#" id="btn-logout" class="flex text-[17px] items-center gap-3 px-4 py-3 hover:bg-[#36B37E]/70 rounded-xl transition">
            <img src="{{ asset('assets/icon/logout.svg') }}" alt="Logout" class="h-5 w-5" />
            <span>Logout</span>
        </a>
        @endif
    </div>
</aside>