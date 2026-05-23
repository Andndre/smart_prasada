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
        Schema::create('templat_hotspot', function (Blueprint $table) {
            $table->id('templat_hotspot_id');
            $table->string('name');
            $table->enum('type', ['navigation', 'info', 'text']);
            $table->string('file_path'); // SVG file path
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_animated')->default(false);
            $table->string('default_color')->nullable();
            $table->timestamps();

            // Index
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templat_hotspot');
    }
};
