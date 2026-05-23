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
        Schema::create('adegan', function (Blueprint $table) {
            $table->id('adegan_id');

            // Reference to existing situs_peninggalan table
            $table->unsignedBigInteger('situs_id');
            $table->foreign('situs_id')
                ->references('situs_id')
                ->on('situs_peninggalan')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('image'); // Panorama image URL or path
            $table->double('camera_x')->default(0);
            $table->double('camera_y')->default(0);
            $table->double('camera_z')->default(0);
            $table->integer('order')->default(0);

            // Type: 'panorama' or 'virtual_museum'
            $table->enum('scene_type', ['panorama', 'virtual_museum'])->default('panorama');

            // Template defaults for new hotspots in this scene
            $table->json('hotspot_defaults')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['situs_id', 'order']);
            $table->index('scene_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adegan');
    }
};
