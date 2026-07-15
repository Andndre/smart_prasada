<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Focus the schema on VR: drop the AR marker table, the AR columns on
     * virtual_museum_object, and the AR marker catalog table.
     */
    public function up(): void
    {
        Schema::table('virtual_museum_object', function (Blueprint $table) {
            $table->dropForeign(['marker_id']);
            $table->dropIndex(['marker_id']);
            $table->dropColumn(['marker_id', 'path_gambar_marker', 'path_patt']);
        });

        Schema::dropIfExists('ar_marker');
        Schema::dropIfExists('katalogs');
    }

    public function down(): void
    {
        Schema::create('katalogs', function (Blueprint $table) {
            $table->id();
            $table->string('path_pdf')->nullable();
            $table->timestamps();
        });

        Schema::create('ar_marker', function (Blueprint $table) {
            $table->id('marker_id');
            $table->foreignId('situs_id')->constrained('situs_peninggalan', 'situs_id')->onDelete('cascade');
            $table->foreignId('museum_id')->constrained('virtual_museum', 'museum_id')->onDelete('cascade');
            $table->string('nama', 255)->nullable();
            $table->string('path_gambar_marker', 255)->nullable()->comment('path gambar marker untuk AR');
            $table->string('path_patt', 255)->nullable()->comment('path file pattern training AR marker');
            $table->timestamps();
        });

        Schema::table('virtual_museum_object', function (Blueprint $table) {
            $table->string('path_patt', 255)->nullable()->comment('path file pattern training AR marker');
            $table->string('path_gambar_marker', 255)->nullable()->comment('path gambar marker untuk AR');
            $table->unsignedBigInteger('marker_id')->nullable();
            $table->foreign('marker_id')->references('marker_id')->on('ar_marker')->onDelete('cascade');
            $table->index('marker_id');
        });
    }
};
