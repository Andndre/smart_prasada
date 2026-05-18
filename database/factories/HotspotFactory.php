<?php

namespace Database\Factories;

use App\Models\Hotspot;
use App\Models\HotspotTemplate;
use App\Models\Scene;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hotspot>
 */
class HotspotFactory extends Factory
{
    protected $model = Hotspot::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scene_id' => Scene::factory(),
            'label' => fake()->words(2, true),
            'position_x' => fake()->randomFloat(2, -10, 10),
            'position_y' => fake()->randomFloat(2, -5, 5),
            'position_z' => fake()->randomFloat(2, -20, -1),
            'rotation_x' => 0,
            'rotation_y' => 0,
            'rotation_z' => 0,
            'target_scene_id' => null,
            'color' => fake()->hexColor(),
            'order' => fake()->numberBetween(0, 10),
            'type' => Hotspot::TYPE_NAVIGATION,
            'modal_title' => null,
            'modal_content' => null,
            'modal_image' => null,
            'template_id' => null,
        ];
    }

    /**
     * Indicate that this is a navigation hotspot.
     */
    public function navigation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Hotspot::TYPE_NAVIGATION,
        ]);
    }

    /**
     * Indicate that this is an info hotspot.
     */
    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Hotspot::TYPE_INFO,
            'modal_title' => fake()->sentence(),
            'modal_content' => fake()->paragraphs(2, true),
        ]);
    }

    /**
     * Indicate that this is a text hotspot.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Hotspot::TYPE_TEXT,
        ]);
    }

    /**
     * Indicate that this is a compass hotspot.
     */
    public function compass(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Hotspot::TYPE_COMPASS,
            'rotation_y' => fake()->numberBetween(0, 360),
        ]);
    }

    /**
     * Set the target scene for navigation hotspots.
     */
    public function withTarget(Scene $scene): static
    {
        return $this->state(fn (array $attributes) => [
            'target_scene_id' => $scene->id,
        ]);
    }
}