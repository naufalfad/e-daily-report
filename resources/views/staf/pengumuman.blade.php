@extends('layouts.app', ['title' => 'Pengumuman', 'role' => 'staf', 'active' => 'pengumuman'])

@section('content')
<section id="pengumuman-staf-root" class="rounded-2xl bg-white ring-1 ring-slate-200 px-6 py-5 flex flex-col h-full min-h-[500px]">

    <h2 class="text-[18px] font-normal mb-6">Papan Pengumuman</h2>

    {{-- STATE 2: KOSONG --}}
    <div id="staf-empty" class="hidden flex-1 flex flex-col items-center justify-center gap-3 py-10">
        <img src="{{ asset('assets/icon/announcement-empty.png') }}" alt="Belum ada pengumuman"
            class="w-[100px] h-[100px] object-contain opacity-60 grayscale">
        <p class="text-[13px] text-slate-400 text-center">
            Belum ada pengumuman terbaru dari atasan.
        </p>
    </div>

    {{-- STATE 3: LIST --}}
    <div id="staf-list" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Cards by JS --}}
    </div>

</section>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", async () => {

        const listEl = document.getElementById("staf-list");
        const emptyEl = document.getElementById("staf-empty");

        try {

            const response = await fetch("/staf/pengumuman/list", {
                headers: { "X-Requested-With": "XMLHttpRequest" },
                credentials: "include"
            });

            if (!response.ok) {
                console.error("API error:", response.status);
                emptyEl.classList.remove("hidden");
                return;
            }

            const result = await response.json();
            const data = result.data ?? [];

            if (data.length === 0) {
                emptyEl.classList.remove("hidden");
                return;
            }

            emptyEl.classList.add("hidden");
            listEl.classList.remove("hidden");

            listEl.innerHTML = data.map(item => {
                const date = new Date(item.created_at);
                const dateStr = new Intl.DateTimeFormat("id-ID", {
                    day: "numeric", month: "long", year: "numeric"
                }).format(date);

                return `
                <article class="rounded-[18px] border border-[#E2E8F0] bg-white px-5 py-4 shadow-sm hover:shadow-md transition-shadow hover:border-[#1C7C54]/50">
                    <h3 class="text-[14px] font-bold text-slate-800 mb-2">${item.judul}</h3>
                    <p class="text-[12px] text-slate-600 mb-4 whitespace-pre-line">
                        ${item.isi_pengumuman}
                    </p>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
                            📅 ${dateStr}
                        </span>
                        <span class="text-[10px] text-slate-400">
                            • Oleh ${item.creator?.name ?? 'Admin'}
                        </span>
                    </div>
                </article>
                `;
            }).join("");

        } catch (err) {
            console.error(err);
            emptyEl.classList.remove("hidden");
        }
    });
</script>

@endpush