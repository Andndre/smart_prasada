<?php

use App\Models\SitusPeninggalan;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;
use Database\Seeders\ElearningContentSeeder;
use Illuminate\Console\Command;

/**
 * Nama node scene-graph di dalam sebuah GLB, dibaca dari chunk JSON di depan
 * berkas: header 12 byte, lalu [panjang:4][jenis:4][data]. Chunk pertama selalu
 * JSON, jadi model 40 MB pun tidak perlu dibaca seluruhnya.
 *
 * @return list<string>
 */
function nodeGlb(string $path): array
{
    $fh = fopen($path, 'rb');
    fread($fh, 12);
    $chunk = unpack('Vlength/Vtype', fread($fh, 8));
    $json = json_decode(fread($fh, $chunk['length']), true, 512, JSON_THROW_ON_ERROR);
    fclose($fh);

    return array_column($json['nodes'] ?? [], 'name');
}

/**
 * Apakah berkas modelnya ada di mesin ini.
 *
 * `storage/app/public/.gitignore` mengabaikan seluruh isinya, jadi 206 MB GLB
 * museum sengaja tidak dilacak git dan CI selalu mendapat direktori kosong.
 * Tes yang mengadu data ke berkas hanya bermakna di mesin yang punya asetnya.
 *
 * Sengaja memeriksa ADA-TIDAKNYA HIMPUNAN aset, bukan tiap berkas satu per satu:
 * kalau asetnya ada tapi satu path meleset, itu justru salah ketik yang harus
 * gagal keras — bukan dilewati diam-diam.
 */
function asetMuseumAda(): bool
{
    return glob(storage_path('app/public/virtual-museum/models/*.glb')) !== [];
}

const ALASAN_LEWAT = 'Berkas GLB museum tidak ada di storage/app/public/virtual-museum/ — salin dulu dari arsip aset.';

describe('ElearningContentSeeder situs', function () {
    beforeEach(function () {
        $this->seed();
    });

    it('seeds only situs that have a museum model', function () {
        expect(SitusPeninggalan::count())->toBe(6)
            ->and(VirtualMuseum::count())->toBe(6)
            ->and(VirtualMuseumObject::count())->toBe(19);

        expect(SitusPeninggalan::doesntHave('virtualMuseum')->count())->toBe(0);
    });

    it('attaches each situs to a materi that exists', function () {
        foreach (SitusPeninggalan::with('materi')->get() as $situs) {
            expect($situs->materi)->not->toBeNull("Situs {$situs->nama} tanpa materi");
        }
    });

    it('does not duplicate rows when run twice', function () {
        (new ElearningContentSeeder)->setContainer(app())->setCommand(
            new Command
        )->run();

        expect(SitusPeninggalan::count())->toBe(6)
            ->and(VirtualMuseum::count())->toBe(6)
            ->and(VirtualMuseumObject::count())->toBe(19);
    });

    /*
     * Bentuk datanya diperiksa tanpa menyentuh berkas, supaya CI tetap menangkap
     * path yang hilang, kosong, atau menunjuk ke luar direktori penyimpanan —
     * kelas kesalahan yang paling mungkin muncul saat menyunting seeder.
     */
    it('keeps every asset path inside the storage folder it is served from', function () {
        $paths = VirtualMuseumObject::pluck('path_obj')
            ->merge(VirtualMuseumObject::pluck('gambar_real'))
            ->merge(VirtualMuseumObject::pluck('path_audio'))
            ->merge(VirtualMuseum::pluck('path_obj'))
            ->filter();

        expect($paths)->toHaveCount(42);

        foreach ($paths as $path) {
            expect($path)->toStartWith('virtual-museum/')
                ->and($path)->not->toContain('..');
        }

        expect(VirtualMuseum::whereNull('path_obj')->count())->toBe(0);
    });

    it('gives a mesh_name to every object in the fully-named scene', function () {
        $rintisan = VirtualMuseum::where('nama', 'Punden Berundak Pura Mehu (Rintisan)')->firstOrFail();

        expect(VirtualMuseumObject::where('museum_id', $rintisan->museum_id)->count())->toBe(7)
            ->and(VirtualMuseumObject::where('museum_id', $rintisan->museum_id)->whereNull('mesh_name')->count())->toBe(0)
            ->and(VirtualMuseumObject::whereNotNull('mesh_name')->count())->toBe(12);
    });

    it('points every path at a file that exists in storage', function () {
        $paths = VirtualMuseum::pluck('path_obj')
            ->merge(VirtualMuseumObject::pluck('path_obj'))
            ->merge(VirtualMuseumObject::pluck('gambar_real'))
            ->merge(VirtualMuseumObject::pluck('path_audio'))
            ->filter();

        foreach ($paths as $path) {
            expect(storage_path('app/public/'.$path))->toBeFile();
        }
    })->skip(fn () => ! asetMuseumAda(), ALASAN_LEWAT);

    it('names a mesh that really exists in the museum GLB', function () {
        $objek = VirtualMuseumObject::with('virtualMuseum')
            ->whereNotNull('mesh_name')
            ->get();

        expect($objek)->not->toBeEmpty();

        foreach ($objek as $o) {
            $node = nodeGlb(storage_path('app/public/'.$o->virtualMuseum->path_obj));

            expect($node)->toContain($o->mesh_name);
        }
    })->skip(fn () => ! asetMuseumAda(), ALASAN_LEWAT);
});
