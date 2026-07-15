<?php

namespace Database\Factories;

use App\Models\SitusPeninggalan;
use App\Models\VirtualMuseum;
use App\Models\VirtualMuseumObject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VirtualMuseumObject>
 */
class VirtualMuseumObjectFactory extends Factory
{
    protected $model = VirtualMuseumObject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'museum_id' => VirtualMuseum::factory(),
            'situs_id' => SitusPeninggalan::factory(),
            'nama' => fake()->word().' Artifact',
            'deskripsi' => fake()->paragraph(),
            'path_obj' => 'virtual-museum/objects/models/'.fake()->uuid().'.glb',
            'gambar_real' => 'images/'.fake()->uuid().'.jpg',
            'path_audio' => null,
        ];
    }
}
