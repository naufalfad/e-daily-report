<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LaporanHarian;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LkhIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test LKH Referensi endpoint.
     */
    public function test_lkh_referensi_endpoint(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found to run test');
        }

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/lkh/referensi');

        $response->assertStatus(200)
            ->assertJsonStructure(['tupoksi', 'list_skp', 'jenis_aktivitas']);
    }

    /**
     * Test LKH creation and update security.
     */
    public function test_lkh_update_prevents_mass_assignment_and_clears_rejection_comments(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found to run test');
        }

        // Create a mock rejected LKH report directly in DB
        $lkh = LaporanHarian::create([
            'user_id' => $user->id,
            'atasan_id' => $user->atasan_id ?? 1,
            'tanggal_laporan' => today()->toDateString(),
            'waktu_mulai' => '08:00',
            'waktu_selesai' => '09:00',
            'jenis_kegiatan' => 'Rapat',
            'kategori_lokasi' => 'WFO',
            'deskripsi_aktivitas' => 'Deskripsi aktivitas rapat koordinasi',
            'output_hasil_kerja' => 'Dokumen',
            'volume' => 1,
            'satuan' => 'Laporan',
            'status' => 'rejected',
            'komentar_validasi' => 'Revisi jam mulai laporan',
            'waktu_validasi' => now(),
            'location_provider' => 'gps_device',
        ]);

        // Attempting to update status to 'approved' directly (Mass Assignment Attack)
        $payload = [
            'deskripsi_aktivitas' => 'Deskripsi aktivitas rapat koordinasi diperbarui',
            'status' => 'approved', // Should be ignored/rejected by validation
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/lkh/update/{$lkh->id}", $payload);

        // Validation should fail because status cannot be 'approved' in user update rules
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Resubmitting correctly with status 'waiting_review'
        $payloadCorrect = [
            'deskripsi_aktivitas' => 'Deskripsi aktivitas rapat koordinasi direvisi',
            'status' => 'waiting_review',
        ];

        $response2 = $this->actingAs($user, 'sanctum')
            ->postJson("/api/lkh/update/{$lkh->id}", $payloadCorrect);

        $response2->assertStatus(200);

        // Verify that the LKH in database has status updated, and comments cleared
        $updatedLkh = LaporanHarian::find($lkh->id);
        $this->assertEquals('waiting_review', $updatedLkh->status);
        $this->assertNull($updatedLkh->komentar_validasi);
        $this->assertNull($updatedLkh->waktu_validasi);
    }
}
