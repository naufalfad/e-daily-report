@extends('layouts.app')

@section('title', 'Master Jabatan')

@section('content')
<div class="w-full px-6 py-6">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Jabatan</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola referensi jabatan pada setiap Unit Kerja.</p>
        </div>
        
        <button onclick="openModal('add')" 
            class="group bg-[#1C7C54] hover:bg-[#166443] text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-700/20 transition-all duration-200 flex items-center gap-2 text-sm font-medium transform active:scale-95">
            <div class="bg-white/20 p-1 rounded-md group-hover:rotate-90 transition-transform">
                <i class="fas fa-plus fa-xs"></i>
            </div>
            Tambah Jabatan
        </button>
    </div>

    {{-- CARD TABEL --}}
    <div class="bg-white rounded-[24px] shadow-sm ring-1 ring-slate-200/60 overflow-hidden">
        
        {{-- Toolbar --}}
        <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="bg-purple-100 text-purple-600 w-10 h-10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div>
                    <h6 class="font-semibold text-slate-700">Daftar Jabatan</h6>
                    <span class="text-xs text-slate-400" id="total-records">Memuat data...</span>
                </div>
            </div>

            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input type="text" id="customSearch" 
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#1C7C54]/20 focus:border-[#1C7C54] sm:text-sm transition-shadow duration-200" 
                    placeholder="Cari jabatan atau unit kerja...">
            </div>
        </div>

        {{-- Table --}}
        <div class="relative">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tableJabatan">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 border-b border-slate-100 w-[5%] text-center">No</th>
                            <th class="px-6 py-4 border-b border-slate-100">Nama Jabatan</th>
                            <th class="px-6 py-4 border-b border-slate-100">Unit Kerja</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Jml. Pegawai</th>
                            <th class="px-6 py-4 border-b border-slate-100 text-center w-[15%]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-slate-600 divide-y divide-slate-100 bg-white"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL --}}
<div id="modalJabatan" class="fixed inset-0 z-[9999] hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] transition-opacity opacity-0" id="modalBackdrop"></div>
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:w-full sm:max-w-lg opacity-0 scale-95" id="modalPanel">
                
                <form id="formJabatan">
                    @csrf
                    <input type="hidden" id="jabatan_id" name="id">
                    <input type="hidden" name="_method" id="method" value="POST">

                    <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center sticky top-0 z-10">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Tambah Jabatan</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tentukan nama jabatan dan unit kerjanya.</p>
                        </div>
                        <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-6 py-6 space-y-5">
                        {{-- Select Unit Kerja --}}
                        <div>
                            <label for="unit_kerja_id" class="block text-sm font-semibold text-slate-700 mb-2">
                                Unit Kerja <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="unit_kerja_id" id="unit_kerja_id" required
                                    class="block w-full rounded-xl border-slate-300 py-3 pl-4 pr-10 text-slate-900 shadow-sm focus:border-[#1C7C54] focus:ring-[#1C7C54] sm:text-sm appearance-none bg-no-repeat bg-[right_1rem_center]">
                                    <option value="" disabled selected>-- Pilih Unit Kerja --</option>
                                    @foreach($unitKerjas as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Input Nama Jabatan --}}
                        <div>
                            <label for="nama_jabatan" class="block text-sm font-semibold text-slate-700 mb-2">
                                Nama Jabatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nama_jabatan" id="nama_jabatan" required
                                class="block w-full rounded-xl border-slate-300 py-3 px-4 text-slate-900 shadow-sm focus:border-[#1C7C54] focus:ring-[#1C7C54] sm:text-sm transition-all"
                                placeholder="Contoh: Sekretaris Dinas">
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                        <button type="submit" 
                            class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-[#1C7C54] px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#166443] transition-all">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-5 py-2.5 text-sm font-medium text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-all">
                            Batal
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let table;

    $(document).ready(function() {
        table = $('#tableJabatan').DataTable({
            processing: true,
            serverSide: false,
            ajax: "{{ route('admin.master.jabatan.index') }}",
            dom: 'rt<"flex flex-col md:flex-row justify-between items-center gap-4 px-6 py-4 border-t border-slate-100"<"text-sm text-slate-500"i><"flex items-center gap-2"p>>',
            language: {
                zeroRecords: `<div class="py-8 flex flex-col items-center justify-center text-center">
                                <img src="{{ asset('assets/tips.svg') }}" class="h-24 w-24 mb-3 opacity-60" alt="No Data">
                                <p class="text-slate-500 font-medium">Belum ada data jabatan.</p>
                              </div>`,
                paginate: { next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' }
            },
            columns: [
                { data: null, render: (data, type, row, meta) => `<span class="font-medium text-slate-500">${meta.row + 1}</span>`, className: "px-6 py-4 align-middle text-center" },
                { data: 'nama_jabatan', className: "px-6 py-4 align-middle font-semibold text-slate-700" },
                { 
                    data: 'unit_kerja.nama_unit', 
                    className: "px-6 py-4 align-middle text-slate-600",
                    render: (data) => data ? `<div class="flex items-center gap-2"><i class="fas fa-building text-slate-400"></i> ${data}</div>` : '-' 
                },
                { 
                    data: 'users_count', 
                    className: "px-6 py-4 align-middle text-center",
                    render: (data) => `<span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-100"><i class="fas fa-users text-[10px]"></i> ${data} Org</span>`
                },
                {
                    data: 'id',
                    className: "px-6 py-4 align-middle text-center",
                    render: function(data, type, row) {
                        return `
                            <div class="flex justify-center gap-2">
                                <button class="group p-2 rounded-lg border border-transparent hover:border-amber-200 hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition-all" 
                                    onclick='openModal("edit", ${JSON.stringify(row)})' title="Edit">
                                    <i class="fas fa-pen-to-square"></i>
                                </button>
                                <button class="group p-2 rounded-lg border border-transparent hover:border-red-200 hover:bg-red-50 text-slate-400 hover:text-red-600 transition-all" 
                                    onclick="deleteData(${data})" title="Hapus">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            drawCallback: function(settings) {
                let api = this.api();
                $('#total-records').text(`Total ${api.data().count()} Jabatan`);
                $('.dataTables_paginate .paginate_button').addClass('px-3 py-1 mx-1 rounded-md text-sm hover:bg-slate-100 text-slate-600 transition cursor-pointer');
                $('.dataTables_paginate .paginate_button.current').addClass('bg-[#1C7C54] text-white hover:bg-[#166443] hover:text-white').removeClass('text-slate-600');
            }
        });

        // Custom Search
        let searchTimer;
        $('#customSearch').on('keyup', function() {
            clearTimeout(searchTimer);
            let value = this.value;
            searchTimer = setTimeout(() => { table.search(value).draw(); }, 300);
        });

        // Submit Form
        $('#formJabatan').on('submit', function(e) {
            e.preventDefault();
            let id = $('#jabatan_id').val();
            let url = id ? `/admin/master/jabatan/${id}` : "{{ route('admin.master.jabatan.store') }}";
            let btn = $(this).find('button[type="submit"]');
            let originalContent = btn.html();

            btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    closeModal();
                    table.ajax.reload();
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                    Toast.fire({ icon: 'success', title: res.message });
                },
                error: function(err) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: err.responseJSON.message || 'Terjadi kesalahan.', confirmButtonColor: '#1C7C54' });
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalContent);
                }
            });
        });
    });

    function openModal(type, data = null) {
        $('#formJabatan')[0].reset();
        if (type === 'add') {
            $('#modalTitle').text('Tambah Jabatan Baru');
            $('#jabatan_id').val('');
            $('#method').val('POST');
            $('#unit_kerja_id').val('').trigger('change');
        } else {
            $('#modalTitle').text('Perbarui Jabatan');
            $('#jabatan_id').val(data.id);
            $('#nama_jabatan').val(data.nama_jabatan);
            $('#unit_kerja_id').val(data.unit_kerja_id).trigger('change');
            $('#method').val('PUT');
        }
        $('#modalJabatan').removeClass('hidden');
        requestAnimationFrame(() => {
            $('#modalBackdrop').removeClass('opacity-0');
            $('#modalPanel').removeClass('opacity-0 scale-95');
        });
    }

    function closeModal() {
        $('#modalBackdrop').addClass('opacity-0');
        $('#modalPanel').addClass('opacity-0 scale-95');
        setTimeout(() => { $('#modalJabatan').addClass('hidden'); }, 200);
    }

    function deleteData(id) {
        Swal.fire({
            title: 'Hapus Jabatan ini?',
            text: "Pastikan tidak ada pegawai yang menjabat.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/master/jabatan/${id}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire({ title: 'Terhapus!', text: res.message, icon: 'success', confirmButtonColor: '#1C7C54' });
                        table.ajax.reload();
                    },
                    error: function(err) {
                        Swal.fire({ title: 'Gagal!', text: err.responseJSON.message, icon: 'error', confirmButtonColor: '#d33' });
                    }
                });
            }
        });
    }
</script>
@endpush