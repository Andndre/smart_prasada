<?php

use App\Models\SitusPeninggalan;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;
use Database\Seeders\ElearningContentSeeder;
use Illuminate\Console\Command;

/**
 * Setiap situs yang di-seed terikat pada berkas GLB/gambar/audio nyata di
 * storage. Path yang salah ketik tidak memunculkan error apa pun — museum
 * hanya gagal memuat di headset, jauh setelah seeder dinyatakan sukses.
 */
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

    it('points every path at a file that exists in storage', function () {
        $paths = VirtualMuseum::pluck('path_obj')
            ->merge(VirtualMuseumObject::pluck('path_obj'))
            ->merge(VirtualMuseumObject::pluck('gambar_real'))
            ->merge(VirtualMuseumObject::pluck('path_audio'))
            ->filter();

        expect($paths)->toHaveCount(42);

        foreach ($paths as $path) {
            expect(storage_path('app/public/'.$path))->toBeFile();
        }
    });

    it('names a mesh that really exists in the museum GLB', function () {
        $objek = VirtualMuseumObject::with('virtualMuseum')
            ->whereNotNull('mesh_name')
            ->get();

        expect($objek)->not->toBeEmpty();

        foreach ($objek as $o) {
            $node = nodeGlb(storage_path('app/public/'.$o->virtualMuseum->path_obj));

            expect($node)->toContain($o->mesh_name);
        }
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
});
