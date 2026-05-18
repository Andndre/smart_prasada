<?php

namespace Database\Factories;

use App\Models\Scene;
use App\Models\SitusPeninggalan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scene>
 */
class SceneFactory extends Factory
{
    protected $model = Scene::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'situs_id' => SitusPeninggalan::factory(),
            'name' => fake()->words(3, true),
            'image' => '/storage/panoramas/' . fake()->uuid() . '.jpg',
            'camera_x' => 0,
            'camera_y' => 0,
            'camera_z' => 0,
            'order' => fake()->numberBetween(0, 10),
            'scene_type' => Scene::TYPE_PANORAMA,
            'hotspot_defaults' => [
                'position_x' => 0,
                'position_y' => -0.5,
                'position_z' => -4,
                'color' => '#00bcd4',
            ],
        ];
    }

    /**
     * Indicate that this is a virtual museum scene.
     */
    public function virtualMuseum(): static
    {
        return $this->state(fn (array $attributes) => [
            'scene_type' => Scene::TYPE_VIRTUAL_MUSEUM,
        ]);
    }

    /**
     * Indicate that this is a panorama scene.
     */
    public function panorama(): static
    {
        return $this->state(fn (array $attributes) => [
            'scene_type' => Scene::TYPE_PANORAMA,
        ]);
    }
}