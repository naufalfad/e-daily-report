<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    // Constructor menerima Collection Data langsung
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Pegawai',
            'Waktu',
            'Jenis Kegiatan',
            'Kategori Lokasi', // [BARU] Header Kategori Lokasi
            'Deskripsi Aktivitas',
            'Output/Hasil',
            'Target SKP',      // [PERBAIKAN] Disesuaikan menjadi Target SKP
            'Status Validasi',
        ];
    }

    public function map($lkh): array
    {
        return [
            // Formatting tanggal untuk memastikan output di Excel tidak berantakan
            $lkh->tanggal_laporan ? $lkh->tanggal_laporan->format('Y-m-d') : '-',
            $lkh->user->name ?? '-',
            $lkh->waktu_mulai . ' - ' . $lkh->waktu_selesai,
            $lkh->jenis_kegiatan,
            
            // [BARU] Pemetaan nilai Kategori Lokasi (WFO, WFH, WFA, DL)
            $lkh->kategori_lokasi ?? 'WFO', 
            
            $lkh->deskripsi_aktivitas,
            $lkh->output_hasil_kerja,
            
            // [PERBAIKAN] Relasi diubah dari 'skp' menjadi 'rencana' sesuai arsitektur terbaru
            $lkh->rencana ? $lkh->rencana->rencana_hasil_kerja : 'Non-SKP',
            
            strtoupper($lkh->status),
        ];
    }
}