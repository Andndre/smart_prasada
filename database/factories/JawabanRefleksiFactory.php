<?php

namespace Database\Factories;

use App\Models\JawabanRefleksi;
use App\Models\PertanyaanRefleksi;
use App\Models\User;
use App\Models\VirtualMuseum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JawabanRefleksi>
 */
class JawabanRefleksiFactory extends Factory
{
    protected $model = JawabanRefleksi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pertanyaan_id' => PertanyaanRefleksi::factory(),
            'user_id' => User::factory(),
            'museum_id' => VirtualMuseum::factory(),
            'kode_responden' => null,
            'sesi_id' => null,
            'jawaban' => fake()->paragraph(),
        ];
    }
}
