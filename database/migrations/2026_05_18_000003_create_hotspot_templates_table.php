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
        Schema::create('hotspot_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['navigation', 'info', 'text', 'compass']);
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
        Schema::dropIfExists('hotspot_templates');
    }
};