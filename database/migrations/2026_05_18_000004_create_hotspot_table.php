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
        Schema::create('hotspot', function (Blueprint $table) {
            $table->id('hotspot_id');
            $table->unsignedBigInteger('adegan_id');
            $table->foreign('adegan_id')
                ->references('adegan_id')
                ->on('adegan')
                ->onDelete('cascade');
            $table->string('label');

            // 3D Position (A-Frame coordinates)
            $table->double('position_x')->default(0);
            $table->double('position_y')->default(-0.5);
            $table->double('position_z')->default(-4);

            // 3D Rotation
            $table->double('rotation_x')->default(0);
            $table->double('rotation_y')->default(0);
            $table->double('rotation_z')->default(0);

            // Navigation target (nullable for info/text type hotspots)
            $table->unsignedBigInteger('target_adegan_id')->nullable();
            $table->foreign('target_adegan_id')
                ->references('adegan_id')
                ->on('adegan')
                ->onDelete('set null');

            $table->string('color')->default('#00bcd4');
            $table->integer('order')->default(0);

            // Type enum: navigation, info, text
            $table->enum('type', ['navigation', 'info', 'text'])
                ->default('navigation');

            // Info modal content (HTML allowed - admin input only)
            $table->string('modal_title')->nullable();
            $table->text('modal_content')->nullable();
            $table->string('modal_image')->nullable();

            // Template reference
            $table->unsignedBigInteger('templat_hotspot_id')->nullable();
            $table->foreign('templat_hotspot_id')
                ->references('templat_hotspot_id')
                ->on('templat_hotspot')
                ->onDelete('set null');

            // Animation configuration
            $table->json('animation_config')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['adegan_id', 'order']);
            $table->index('target_adegan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot');
    }
};
