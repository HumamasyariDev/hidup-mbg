<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\School;
use App\Models\SppgProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<School>
 */
final class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'id'                      => Str::uuid()->toString(),
            'name'                    => fake()->randomElement(['SDN', 'SMPN', 'SMAN', 'SMK']) . ' ' . fake('id_ID')->city() . ' ' . fake()->numberBetween(1, 50),
            'npsn'                    => fake()->unique()->numerify('########'),
            'address'                 => fake('id_ID')->address(),
            'city'                    => fake('id_ID')->city(),
            'province'                => fake()->randomElement([
                'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur',
                'Banten', 'DI Yogyakarta',
            ]),
            'phone'                   => fake('id_ID')->phoneNumber(),
            'email'                   => fake()->unique()->safeEmail(),
            'level'                   => fake()->randomElement(['sd', 'smp', 'sma', 'smk']),
            'student_count'           => fake()->numberBetween(100, 1500),
            'geofence_radius_meters'  => fake()->randomElement([50, 100, 150, 200]),
            'sppg_provider_id'        => SppgProvider::factory(),
            'is_active'               => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
