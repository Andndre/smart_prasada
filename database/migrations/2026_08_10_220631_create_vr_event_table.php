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
        Schema::create('vr_event', function (Blueprint $table) {
            $table->id('event_id');
            $table->uuid('sesi_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('museum_id')->constrained('virtual_museum', 'museum_id')->onDelete('cascade');

            // Identitas responden untuk mode kiosk: di lapangan satu headset dipakai
            // ratusan orang lewat satu akun, jadi user_id tidak membedakan siapa pun.
            // Tanpa kolom ini, data sesi tidak bisa disilangkan dengan angket dan pretest.
            $table->string('kode_responden')->nullable();

            $table->string('jenis');

            // Bukan foreign key: nama mesh bisa ada di GLB tanpa punya baris di
            // virtual_museum_object, dan event tetap harus tercatat kalau itu terjadi.
            $table->string('mesh_name')->nullable();

            $table->json('detail')->nullable();

            // Milidetik sejak sesi dimulai, diukur klien dengan performance.now().
            // Wajib dari sisi klien: event dikirim batch, jadi created_at server akan
            // memberi timestamp yang sama untuk semua event dalam satu batch dan
            // menghancurkan metrik time-on-task.
            $table->unsignedBigInteger('offset_ms');

            $table->timestamp('created_at')->useCurrent();

            $table->index(['sesi_id', 'offset_ms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vr_event');
    }
};
