<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jawaban_refleksi', function (Blueprint $table) {
            $table->id('jawaban_id');
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan_refleksi', 'pertanyaan_id')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('museum_id')->constrained('virtual_museum', 'museum_id')->onDelete('cascade');

            // Kunci sambung utama ke vr_event. Di mode kiosk ratusan responden memakai
            // satu akun, jadi user_id tidak membedakan siapa pun — tanpa kolom ini
            // refleksi tidak bisa disilangkan dengan perilaku di dalam VR, dan itulah
            // inti klaim experiential learning proposal.
            $table->string('kode_responden')->nullable();

            // Sambungan tepat ke satu sesi VR. Nullable karena hanya tersedia saat
            // refleksi dibuka dari perangkat yang sama dengan sesi VR-nya (jalur HP).
            // Di jalur headset kiosk, fasilitator membuka refleksi di perangkat lain,
            // jadi penyambungannya lewat kode_responden + waktu. Perlu karena tiap
            // responden mengikuti 2 kegiatan; kode saja akan melebur keduanya.
            $table->uuid('sesi_id')->nullable();

            $table->text('jawaban');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['kode_responden', 'created_at']);
            $table->index('sesi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_refleksi');
    }
};
