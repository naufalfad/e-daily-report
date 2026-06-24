<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SkpRencana;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SkpIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test SKP creation rejects non-positive targets.
     */
    public function test_skp_creation_rejects_negative_and_zero_targets(): void
    {
        $user = User::first();
        if (!$user) {
            $this->markTestSkipped('No user found to run test');
        }

        // 1. Zero target validation test
        $payloadZero = [
            'periode_awal' => today()->toDateString(),
            'periode_akhir' => today()->addMonths(1)->toDateString(),
            'rhk_intervensi' => 'RHK Pimpinan',
            'rencana_hasil_kerja' => 'Rencana Hasil Kerja Staf',
            'targets' => [
                [
                    'jenis_aspek' => 'Kuantitas',
                    'indikator' => 'Jumlah Laporan',
                    'target' => 0, // Invalid: must be >= 1
                    'satuan' => 'Dokumen',
                ]
            ]
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/skp', $payloadZero);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['targets.0.target']);

        // 2. Negative target validation test
        $payloadNegative = $payloadZero;
        $payloadNegative['targets'][0]['target'] = -5; // Invalid

        $responseNegative = $this->actingAs($user, 'sanctum')
            ->postJson('/api/skp', $payloadNegative);

        $responseNegative->assertStatus(422)
            ->assertJsonValidationErrors(['targets.0.target']);

        // 3. Valid target test (must pass)
        $payloadValid = $payloadZero;
        $payloadValid['targets'][0]['target'] = 5; // Valid

        $responseValid = $this->actingAs($user, 'sanctum')
            ->postJson('/api/skp', $payloadValid);

        $responseValid->assertStatus(201);
    }
}
