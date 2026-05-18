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
        Schema::create('hotspots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scene_id');
            $table->foreign('scene_id')
                ->references('id')
                ->on('scenes')
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
            $table->unsignedBigInteger('target_scene_id')->nullable();
            $table->foreign('target_scene_id')
                ->references('id')
                ->on('scenes')
                ->onDelete('null');

            $table->string('color')->default('#00bcd4');
            $table->integer('order')->default(0);

            // Type enum: navigation, info, text, compass
            $table->enum('type', ['navigation', 'info', 'text', 'compass'])
                ->default('navigation');

            // Info modal content (HTML allowed - admin input only)
            $table->string('modal_title')->nullable();
            $table->text('modal_content')->nullable();
            $table->string('modal_image')->nullable();

            // Template reference
            $table->unsignedBigInteger('template_id')->nullable();
            $table->foreign('template_id')
                ->references('id')
                ->on('hotspot_templates')
                ->onDelete('set null');

            // Animation configuration
            $table->json('animation_config')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['scene_id', 'order']);
            $table->index('target_scene_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspots');
    }
};