<?php

use App\Enums\NilaiKarakter;
use App\Models\PertanyaanRefleksi;
use App\Models\VirtualMuseum;
use Database\Seeders\PertanyaanRefleksiSeeder;

describe('PertanyaanRefleksiSeeder', function () {
    beforeEach(function () {
        $this->seed();
    });

    it('gives every museum questions so the refleksi module can be tested', function () {
        expect(PertanyaanRefleksi::distinct('museum_id')->count('museum_id'))
            ->toBe(VirtualMuseum::count())
            ->and(PertanyaanRefleksi::count())->toBe(18);
    });

    it('asks about exactly one nilai karakter from the enum per question', function () {
        foreach (PertanyaanRefleksi::all() as $p) {
            expect($p->nilai_karakter)->toBeInstanceOf(NilaiKarakter::class);
        }
    });

    it('numbers the questions from one within each museum', function () {
        foreach (PertanyaanRefleksi::all()->groupBy('museum_id') as $museum) {
            expect($museum->pluck('urutan')->sort()->values()->all())->toBe([1, 2, 3]);
        }
    });

    /**
     * Gerbang utamanya. Tim materi mengarang lewat CRUD admin dan karangan itu
     * tidak punya salinan; satu `db:seed` yang menimpanya menghapus pekerjaan
     * yang tidak bisa dikembalikan.
     */
    it('never overwrites questions the content team already wrote', function () {
        $museum = VirtualMuseum::first();
        PertanyaanRefleksi::where('museum_id', $museum->museum_id)->delete();

        $karangan = PertanyaanRefleksi::create([
            'museum_id' => $museum->museum_id,
            'nilai_karakter' => NilaiKarakter::Mandiri,
            'pertanyaan' => 'Pertanyaan karangan tim materi.',
            'urutan' => 1,
        ]);

        (new PertanyaanRefleksiSeeder)->setContainer(app())->run();

        $tersisa = PertanyaanRefleksi::where('museum_id', $museum->museum_id)->get();

        expect($tersisa)->toHaveCount(1)
            ->and($tersisa->first()->pertanyaan_id)->toBe($karangan->pertanyaan_id)
            ->and($tersisa->first()->pertanyaan)->toBe('Pertanyaan karangan tim materi.');
    });

    it('does not pile up duplicates when run twice', function () {
        (new PertanyaanRefleksiSeeder)->setContainer(app())->run();

        expect(PertanyaanRefleksi::count())->toBe(18);
    });
});
