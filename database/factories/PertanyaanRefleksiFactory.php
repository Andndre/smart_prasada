<?php

namespace Database\Factories;

use App\Enums\NilaiKarakter;
use App\Models\PertanyaanRefleksi;
use App\Models\VirtualMuseum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PertanyaanRefleksi>
 */
class PertanyaanRefleksiFactory extends Factory
{
    protected $model = PertanyaanRefleksi::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'museum_id' => VirtualMuseum::factory(),
            'nilai_karakter' => fake()->randomElement(NilaiKarakter::cases()),
            'pertanyaan' => fake()->sentence().'?',
            'urutan' => fake()->numberBetween(0, 10),
        ];
    }
}
