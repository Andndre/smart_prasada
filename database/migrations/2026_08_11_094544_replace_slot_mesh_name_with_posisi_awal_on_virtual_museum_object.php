<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Definisi puzzle pindah dari GLB ke data editor.
 *
 * Sebelumnya potongan puzzle butuh mesh penanda tak terlihat di dalam GLB, namanya
 * diisi ke `slot_mesh_name`. Setiap koreksi posisi sekecil apa pun berarti buka
 * Blender, geser penanda, ekspor ulang, unggah ulang.
 *
 * Sekarang model selalu diekspor dalam keadaan SUDAH TERPASANG, jadi posisi
 * terpasang sebuah potongan adalah transform bawaannya di GLB — nol data. Yang
 * perlu disimpan hanya `posisi_awal`: selisih ke tempat potongan itu mulai
 * terlepas. Editor visual yang menetapkannya.
 *
 * Selisih, bukan koordinat absolut. `vr-editor.js` memuat GLB apa adanya sementara
 * `vr-museum.js` menggeser seluruh model dengan `position.y -= box.min.y`, jadi
 * koordinat absolut yang disimpan dari editor akan mendarat meleset di runtime
 * tanpa error apa pun. Selisih kebal terhadap transform root mana pun, dan tetap
 * benar setelah model diekspor ulang dengan origin sama.
 *
 * `slot_mesh_name` dibuang, bukan dibiarkan berdampingan: nol dari 30 baris
 * memakainya, jadi ini penggantian bersih. Dua mekanisme paralel hanya akan jadi
 * sumber kebingungan tentang mana yang menang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_museum_object', function (Blueprint $table) {
            $table->json('posisi_awal')->nullable()->after('mesh_name');

            // filemtime GLB saat posisi disimpan. Dipakai editor untuk memperingatkan
            // "model diganti setelah posisi ini disimpan". Nama node yang sama dengan
            // geometri berbeda tidak bisa dideteksi secara umum; ini aproksimasinya.
            $table->unsignedInteger('model_mtime')->nullable()->after('posisi_awal');

            $table->dropColumn('slot_mesh_name');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_museum_object', function (Blueprint $table) {
            $table->string('slot_mesh_name')->nullable()->after('mesh_name');
            $table->dropColumn(['posisi_awal', 'model_mtime']);
        });
    }
};
