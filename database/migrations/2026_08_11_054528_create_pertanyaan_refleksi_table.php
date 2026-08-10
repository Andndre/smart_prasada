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
        Schema::create('pertanyaan_refleksi', function (Blueprint $table) {
            $table->id('pertanyaan_id');
            $table->foreignId('museum_id')->constrained('virtual_museum', 'museum_id')->onDelete('cascade');

            // PERHATIKAN BENTUKNYA: di sini `nilai_karakter` adalah string tunggal,
            // sementara di virtual_museum_object ia array JSON. Bukan kelalaian —
            // satu objek peninggalan membawa beberapa nilai sekaligus (punden berundak
            // sekaligus religius dan gotong royong), tapi satu pertanyaan reflektif
            // menggali tepat satu nilai supaya jawabannya bisa dianalisis per nilai.
            // Sama-sama divalidasi terhadap App\Enums\NilaiKarakter.
            $table->string('nilai_karakter');

            $table->text('pertanyaan');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['museum_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanyaan_refleksi');
    }
};
