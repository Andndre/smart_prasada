<?php

namespace Database\Factories;

use App\Models\HotspotTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotspotTemplate>
 */
class HotspotTemplateFactory extends Factory
{
    protected $model = HotspotTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Template',
            'type' => fake()->randomElement(HotspotTemplate::getTypes()),
            'file_path' => '/hotspots/' . fake()->word() . '.svg',
            'thumbnail_path' => '/hotspots/thumbnails/' . fake()->word() . '.png',
            'is_animated' => fake()->boolean(30),
            'default_color' => fake()->hexColor(),
        ];
    }

    /**
     * Indicate that this is a navigation template.
     */
    public function navigation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => HotspotTemplate::TYPE_NAVIGATION,
            'name' => 'Navigation Template',
            'file_path' => '/hotspots/nav.svg',
        ]);
    }

    /**
     * Indicate that this is an info template.
     */
    public function info(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => HotspotTemplate::TYPE_INFO,
            'name' => 'Info Template',
            'file_path' => '/hotspots/info.svg',
        ]);
    }

    /**
     * Indicate that this is an animated template.
     */
    public function animated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_animated' => true,
        ]);
    }
}