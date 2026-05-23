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
        Schema::create('aset_hotspots', function (Blueprint $table) {
            $table->id('aset_id');
            $table->string('nama');
            $table->string('file_path');
            $table->string('tipe')->default('image'); // 'image' or 'video'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aset_hotspots');
    }
};
