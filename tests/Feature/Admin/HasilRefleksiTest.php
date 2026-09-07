<?php

use App\Enums\JenisEventVr;
use App\Models\JawabanRefleksi;
use App\Models\PertanyaanRefleksi;
use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VrEvent;

describe('Admin Hasil Refleksi', function () {
    it('allows admin to view hasil refleksi page', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();
        $soal = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        JawabanRefleksi::create([
            'pertanyaan_id' => $soal->pertanyaan_id,
            'user_id' => $admin->id,
            'museum_id' => $museum->museum_id,
            'kode_responden' => 'R001',
            'jawaban' => 'Refleksi siswa pertama yang mendalam.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.hasil-refleksi', $museum->museum_id));

        $response->assertSuccessful();
        $response->assertSee('Hasil Jawaban Refleksi');
        $response->assertSee('R001');
        $response->assertSee('Refleksi siswa pertama yang mendalam.');
        $response->assertSee('Unduh CSV Refleksi');
    });

    it('forbids regular users from accessing hasil refleksi', function () {
        $user = User::factory()->create(['role' => 'user']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.hasil-refleksi', $museum->museum_id))
            ->assertRedirect(route('guest.home'));
    });

    it('filters reflection answers by search keyword', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();
        $soal = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        JawabanRefleksi::create([
            'pertanyaan_id' => $soal->pertanyaan_id,
            'user_id' => $admin->id,
            'museum_id' => $museum->museum_id,
            'kode_responden' => 'R001',
            'jawaban' => 'Saya belajar tentang toleransi beragama.',
            'created_at' => now(),
        ]);

        JawabanRefleksi::create([
            'pertanyaan_id' => $soal->pertanyaan_id,
            'user_id' => $admin->id,
            'museum_id' => $museum->museum_id,
            'kode_responden' => 'R002',
            'jawaban' => 'Arsitektur candi sangat megah.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.hasil-refleksi', [
                'museum_id' => $museum->museum_id,
                'search' => 'toleransi',
            ]));

        $response->assertSuccessful();
        $response->assertSee('R001');
        $response->assertSee('toleransi');
        $response->assertDontSee('Arsitektur candi sangat megah');
    });

    it('allows admin to delete a reflection answer', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();
        $soal = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        $jawaban = JawabanRefleksi::create([
            'pertanyaan_id' => $soal->pertanyaan_id,
            'user_id' => $admin->id,
            'museum_id' => $museum->museum_id,
            'kode_responden' => 'R099',
            'jawaban' => 'Data testing yang mau dihapus.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.jawaban-refleksi.destroy', $jawaban->jawaban_id));

        $response->assertRedirect(route('admin.hasil-refleksi', $museum->museum_id));
        expect(JawabanRefleksi::find($jawaban->jawaban_id))->toBeNull();
    });

    it('exports reflection answers filtered by museum', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum1 = VirtualMuseum::factory()->create(['nama' => 'Museum Candi Satu']);
        $museum2 = VirtualMuseum::factory()->create(['nama' => 'Museum Candi Dua']);
        $soal1 = PertanyaanRefleksi::factory()->create(['museum_id' => $museum1->museum_id]);
        $soal2 = PertanyaanRefleksi::factory()->create(['museum_id' => $museum2->museum_id]);

        JawabanRefleksi::create([
            'pertanyaan_id' => $soal1->pertanyaan_id,
            'user_id' => $admin->id,
            'museum_id' => $museum1->museum_id,
            'kode_responden' => 'R001',
            'jawaban' => 'Jawaban untuk museum satu.',
            'created_at' => now(),
        ]);

        JawabanRefleksi::create([
            'pertanyaan_id' => $soal2->pertanyaan_id,
            'user_id' => $admin->id,
            'museum_id' => $museum2->museum_id,
            'kode_responden' => 'R002',
            'jawaban' => 'Jawaban untuk museum dua.',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.refleksi.export', ['museum' => $museum1->museum_id]));

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');

        $content = $response->streamedContent();
        expect($content)->toContain('Jawaban untuk museum satu.');
        expect($content)->not->toContain('Jawaban untuk museum dua.');
    });

    it('exports vr events filtered by museum', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum1 = VirtualMuseum::factory()->create();
        $museum2 = VirtualMuseum::factory()->create();

        VrEvent::create([
            'sesi_id' => '11111111-1111-1111-1111-111111111111',
            'user_id' => $admin->id,
            'museum_id' => $museum1->museum_id,
            'kode_responden' => 'R001',
            'jenis' => JenisEventVr::SesiMulai,
            'offset_ms' => 0,
            'created_at' => now(),
        ]);

        VrEvent::create([
            'sesi_id' => '22222222-2222-2222-2222-222222222222',
            'user_id' => $admin->id,
            'museum_id' => $museum2->museum_id,
            'kode_responden' => 'R002',
            'jenis' => JenisEventVr::SesiMulai,
            'offset_ms' => 0,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.vr-events.export', ['museum' => $museum1->museum_id]));

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');

        $content = $response->streamedContent();
        expect($content)->toContain('11111111-1111-1111-1111-111111111111');
        expect($content)->not->toContain('22222222-2222-2222-2222-222222222222');
    });
});
