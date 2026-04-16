<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SppgProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SppgProvider>
 */
final class SppgProviderFactory extends Factory
{
    protected $model = SppgProvider::class;

    public function definition(): array
    {
        return [
            'id'               => Str::uuid()->toString(),
            'name'             => 'SPPG ' . fake('id_ID')->company(),
            'license_number'   => 'SPPG-' . fake()->numerify('########'),
            'address'          => fake('id_ID')->address(),
            'city'             => fake('id_ID')->city(),
            'province'         => fake()->randomElement([
                'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur',
                'Banten', 'DI Yogyakarta', 'Sumatera Utara', 'Sulawesi Selatan',
            ]),
            'phone'            => fake('id_ID')->phoneNumber(),
            'email'            => fake()->unique()->companyEmail(),
            'is_active'        => true,
            'capacity_per_day' => fake()->numberBetween(500, 5000),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
