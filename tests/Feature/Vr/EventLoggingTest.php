<?php

use App\Enums\JenisEventVr;
use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VrEvent;

describe('POST /vr/events', function () {
    it('stores a batch with its session id and client offsets', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $sesiId = fake()->uuid();

        $this->actingAs($user)
            ->postJson(route('vr.events.store'), [
                'sesi_id' => $sesiId,
                'museum_id' => $museum->museum_id,
                'kode_responden' => 'RESP-014',
                'events' => [
                    ['jenis' => 'sesi_mulai', 'offset_ms' => 0, 'detail' => ['perangkat' => 'headset']],
                    ['jenis' => 'objek_dilihat', 'offset_ms' => 4200, 'mesh_name' => 'Padma_Kurung'],
                    ['jenis' => 'objek_dilepas', 'offset_ms' => 9100, 'mesh_name' => 'Motif_A', 'detail' => ['berhasil' => false]],
                ],
            ])
            ->assertNoContent();

        expect(VrEvent::count())->toBe(3);

        $events = VrEvent::orderBy('offset_ms')->get();
        expect($events->pluck('sesi_id')->unique()->all())->toBe([$sesiId]);
        expect($events->pluck('kode_responden')->unique()->all())->toBe(['RESP-014']);
        expect($events->pluck('offset_ms')->all())->toBe([0, 4200, 9100]);
        expect($events[0]->jenis)->toBe(JenisEventVr::SesiMulai);
        expect($events[0]->detail)->toBe(['perangkat' => 'headset']);
        expect($events[2]->detail)->toBe(['berhasil' => false]);
    });

    it('accepts a batch without a responden code', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($user)
            ->postJson(route('vr.events.store'), [
                'sesi_id' => fake()->uuid(),
                'museum_id' => $museum->museum_id,
                'events' => [['jenis' => 'teleport', 'offset_ms' => 120]],
            ])
            ->assertNoContent();

        expect(VrEvent::sole()->kode_responden)->toBeNull();
    });

    it('rejects an event type outside the enum', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($user)
            ->postJson(route('vr.events.store'), [
                'sesi_id' => fake()->uuid(),
                'museum_id' => $museum->museum_id,
                'events' => [['jenis' => 'objek_dijilat', 'offset_ms' => 10]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['events.0.jenis']);

        expect(VrEvent::count())->toBe(0);
    });

    it('rejects a batch larger than the cap', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        $events = array_fill(0, 201, ['jenis' => 'teleport', 'offset_ms' => 1]);

        $this->actingAs($user)
            ->postJson(route('vr.events.store'), [
                'sesi_id' => fake()->uuid(),
                'museum_id' => $museum->museum_id,
                'events' => $events,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['events']);

        expect(VrEvent::count())->toBe(0);
    });

    it('rejects a negative offset', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($user)
            ->postJson(route('vr.events.store'), [
                'sesi_id' => fake()->uuid(),
                'museum_id' => $museum->museum_id,
                'events' => [['jenis' => 'teleport', 'offset_ms' => -1]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['events.0.offset_ms']);
    });

    it('is not accessible to guests', function () {
        $museum = VirtualMuseum::factory()->create();

        $this->postJson(route('vr.events.store'), [
            'sesi_id' => fake()->uuid(),
            'museum_id' => $museum->museum_id,
            'events' => [['jenis' => 'teleport', 'offset_ms' => 1]],
        ])->assertUnauthorized();

        expect(VrEvent::count())->toBe(0);
    });
});

describe('GET /admin/vr-events/export', function () {
    it('streams every event as CSV', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $sesiId = fake()->uuid();
        $museum = VirtualMuseum::factory()->create();

        VrEvent::factory()->create([
            'sesi_id' => $sesiId,
            'museum_id' => $museum->museum_id,
            'kode_responden' => 'RESP-002',
            'jenis' => JenisEventVr::PuzzleBenar,
            'mesh_name' => 'Motif_Ceplok_Bunga_A',
            'offset_ms' => 51000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.vr-events.export'));

        $response->assertSuccessful();
        $csv = $response->streamedContent();

        expect($csv)->toContain('sesi_id,kode_responden');
        expect($csv)->toContain('RESP-002');
        expect($csv)->toContain('Motif_Ceplok_Bunga_A');
        expect($csv)->toContain('51000');
    });

    it('resolves the device from the session-start event onto every row', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();
        $sesiId = fake()->uuid();

        VrEvent::factory()->create([
            'sesi_id' => $sesiId,
            'museum_id' => $museum->museum_id,
            'jenis' => JenisEventVr::SesiMulai,
            'detail' => ['perangkat' => 'headset'],
            'offset_ms' => 0,
        ]);
        VrEvent::factory()->create([
            'sesi_id' => $sesiId,
            'museum_id' => $museum->museum_id,
            'jenis' => JenisEventVr::Teleport,
            'detail' => null,
            'offset_ms' => 3000,
        ]);

        $csv = $this->actingAs($admin)->get(route('admin.vr-events.export'))->streamedContent();

        $baris = array_values(array_filter(explode("\n", trim($csv))));
        expect($baris)->toHaveCount(3);
        // Baris teleport tidak menyimpan perangkat di detail-nya sendiri.
        expect($baris[2])->toContain('headset');
    });

    it('is not accessible for regular users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.vr-events.export'))
            ->assertRedirect();
    });
});
