<?php

use App\Enums\NilaiKarakter;
use App\Models\JawabanRefleksi;
use App\Models\PertanyaanRefleksi;
use App\Models\User;
use App\Models\VirtualMuseum;

describe('GET /refleksi/{museum_id}', function () {
    it('shows the questions for that museum in order', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        PertanyaanRefleksi::factory()->create([
            'museum_id' => $museum->museum_id,
            'pertanyaan' => 'Pertanyaan kedua?',
            'urutan' => 2,
        ]);
        PertanyaanRefleksi::factory()->create([
            'museum_id' => $museum->museum_id,
            'pertanyaan' => 'Pertanyaan pertama?',
            'urutan' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('refleksi.show', $museum->museum_id));

        $response->assertSuccessful();
        $response->assertSeeInOrder(['Pertanyaan pertama?', 'Pertanyaan kedua?']);
    });

    it('hides the app navigation so the shared kiosk account is not on display', function () {
        $user = User::factory()->create(['name' => 'Fasilitator Kiosk']);
        $museum = VirtualMuseum::factory()->create();
        PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        $this->actingAs($user)->get(route('refleksi.show', $museum->museum_id))
            ->assertDontSee($user->name);

        $this->actingAs($user)->get(route('refleksi.selesai'))
            ->assertDontSee($user->name);
    });

    it('does not show questions belonging to another museum', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        PertanyaanRefleksi::factory()->create([
            'museum_id' => $museum->museum_id,
            'pertanyaan' => 'Milik museum ini',
        ]);
        PertanyaanRefleksi::factory()->create(['pertanyaan' => 'Milik museum lain']);

        $this->actingAs($user)
            ->get(route('refleksi.show', $museum->museum_id))
            ->assertSee('Milik museum ini')
            ->assertDontSee('Milik museum lain');
    });

    it('explains itself when the museum has no questions yet', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($user)
            ->get(route('refleksi.show', $museum->museum_id))
            ->assertSuccessful()
            ->assertSee('Belum ada pertanyaan refleksi');
    });

    it('is not accessible to guests', function () {
        $museum = VirtualMuseum::factory()->create();

        $this->get(route('refleksi.show', $museum->museum_id))->assertRedirect(route('login'));
    });
});

describe('POST /refleksi/{museum_id}', function () {
    it('stores answers with the responden code and session id', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $soal = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);
        $sesiId = fake()->uuid();

        $this->actingAs($user)
            ->post(route('refleksi.store', $museum->museum_id), [
                'kode_responden' => 'R042',
                'sesi_id' => $sesiId,
                'jawaban' => [$soal->pertanyaan_id => 'Gotong royong terlihat saat kerja bakti di kampung.'],
            ])
            ->assertRedirect(route('refleksi.selesai'));

        $jawaban = JawabanRefleksi::sole();
        expect($jawaban->kode_responden)->toBe('R042');
        expect($jawaban->sesi_id)->toBe($sesiId);
        expect($jawaban->museum_id)->toBe($museum->museum_id);
        expect($jawaban->jawaban)->toContain('kerja bakti');
    });

    it('accepts a submission without a responden code or session', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $soal = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        $this->actingAs($user)
            ->post(route('refleksi.store', $museum->museum_id), [
                'jawaban' => [$soal->pertanyaan_id => 'Jawaban tanpa kode.'],
            ])
            ->assertRedirect(route('refleksi.selesai'));

        expect(JawabanRefleksi::sole()->kode_responden)->toBeNull();
    });

    it('skips blank answers instead of storing empty rows', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $diisi = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);
        $kosong = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        $this->actingAs($user)->post(route('refleksi.store', $museum->museum_id), [
            'jawaban' => [
                $diisi->pertanyaan_id => 'Terisi.',
                $kosong->pertanyaan_id => '   ',
            ],
        ]);

        expect(JawabanRefleksi::count())->toBe(1);
        expect(JawabanRefleksi::sole()->pertanyaan_id)->toBe($diisi->pertanyaan_id);
    });

    it('ignores answers aimed at another museum question', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $milikOrangLain = PertanyaanRefleksi::factory()->create();

        $this->actingAs($user)->post(route('refleksi.store', $museum->museum_id), [
            'jawaban' => [$milikOrangLain->pertanyaan_id => 'Kiriman karangan.'],
        ]);

        expect(JawabanRefleksi::count())->toBe(0);
    });

    it('rejects an answer longer than the cap', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $soal = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        $this->actingAs($user)
            ->post(route('refleksi.store', $museum->museum_id), [
                'jawaban' => [$soal->pertanyaan_id => str_repeat('a', 2001)],
            ])
            ->assertSessionHasErrors('jawaban.'.$soal->pertanyaan_id);

        expect(JawabanRefleksi::count())->toBe(0);
    });

    it('rejects a malformed session id', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();
        $soal = PertanyaanRefleksi::factory()->create(['museum_id' => $museum->museum_id]);

        $this->actingAs($user)
            ->post(route('refleksi.store', $museum->museum_id), [
                'sesi_id' => 'bukan-uuid',
                'jawaban' => [$soal->pertanyaan_id => 'Jawaban.'],
            ])
            ->assertSessionHasErrors('sesi_id');
    });
});

describe('admin pertanyaan refleksi', function () {
    it('lists, creates, updates and deletes questions', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.pertanyaan-refleksi', $museum->museum_id))
            ->assertSuccessful();

        $this->actingAs($admin)
            ->post(route('admin.pertanyaan-refleksi.store', $museum->museum_id), [
                'nilai_karakter' => NilaiKarakter::GotongRoyong->value,
                'pertanyaan' => 'Di mana kamu menerapkan gotong royong?',
                'urutan' => 1,
            ])
            ->assertRedirect(route('admin.pertanyaan-refleksi', $museum->museum_id));

        $soal = PertanyaanRefleksi::sole();
        expect($soal->nilai_karakter)->toBe(NilaiKarakter::GotongRoyong);

        $this->actingAs($admin)
            ->put(route('admin.pertanyaan-refleksi.update', $soal->pertanyaan_id), [
                'nilai_karakter' => NilaiKarakter::Religius->value,
                'pertanyaan' => 'Pertanyaan diperbarui?',
                'urutan' => 2,
            ])
            ->assertRedirect(route('admin.pertanyaan-refleksi', $museum->museum_id));

        expect($soal->fresh()->pertanyaan)->toBe('Pertanyaan diperbarui?');

        $this->actingAs($admin)
            ->delete(route('admin.pertanyaan-refleksi.destroy', $soal->pertanyaan_id))
            ->assertRedirect(route('admin.pertanyaan-refleksi', $museum->museum_id));

        expect(PertanyaanRefleksi::count())->toBe(0);
    });

    it('rejects a nilai karakter outside the enum', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.pertanyaan-refleksi.store', $museum->museum_id), [
                'nilai_karakter' => 'nilai_karangan',
                'pertanyaan' => 'Pertanyaan.',
            ])
            ->assertSessionHasErrors('nilai_karakter');

        expect(PertanyaanRefleksi::count())->toBe(0);
    });

    it('is not accessible for regular users', function () {
        $user = User::factory()->create();
        $museum = VirtualMuseum::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.pertanyaan-refleksi', $museum->museum_id))
            ->assertRedirect();
    });
});

describe('GET /admin/refleksi/export', function () {
    it('streams answers joined with their question', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $soal = PertanyaanRefleksi::factory()->create([
            'nilai_karakter' => NilaiKarakter::GotongRoyong,
            'pertanyaan' => 'Pertanyaan yang diekspor?',
        ]);
        JawabanRefleksi::factory()->create([
            'pertanyaan_id' => $soal->pertanyaan_id,
            'museum_id' => $soal->museum_id,
            'kode_responden' => 'R007',
            'jawaban' => 'Jawaban yang diekspor.',
        ]);

        $csv = $this->actingAs($admin)->get(route('admin.refleksi.export'))->streamedContent();

        expect($csv)->toContain('kode_responden');
        expect($csv)->toContain('R007');
        expect($csv)->toContain('gotong_royong');
        expect($csv)->toContain('Pertanyaan yang diekspor?');
        expect($csv)->toContain('Jawaban yang diekspor.');
    });

    it('is not accessible for regular users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.refleksi.export'))->assertRedirect();
    });
});
