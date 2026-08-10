<?php

namespace Database\Factories;

use App\Enums\JenisEventVr;
use App\Models\User;
use App\Models\VirtualMuseum;
use App\Models\VrEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VrEvent>
 */
class VrEventFactory extends Factory
{
    protected $model = VrEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sesi_id' => fake()->uuid(),
            'user_id' => User::factory(),
            'museum_id' => VirtualMuseum::factory(),
            'kode_responden' => null,
            'jenis' => fake()->randomElement(JenisEventVr::cases()),
            'mesh_name' => null,
            'detail' => null,
            'offset_ms' => fake()->numberBetween(0, 600_000),
        ];
    }
}
