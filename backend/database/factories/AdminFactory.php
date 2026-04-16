<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Admin>
 */
final class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'id'                => Str::uuid()->toString(),
            'name'              => fake('id_ID')->name(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => Hash::make('password'),
            'role'              => fake()->randomElement(['super_admin', 'admin_sppg', 'admin_school']),
            'entity_id'         => null,
            'entity_type'       => null,
            'is_active'         => true,
            'email_verified_at' => now(),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role'        => 'super_admin',
            'entity_id'   => null,
            'entity_type' => null,
        ]);
    }

    public function adminSppg(string $sppgProviderId): static
    {
        return $this->state(fn () => [
            'role'        => 'admin_sppg',
            'entity_id'   => $sppgProviderId,
            'entity_type' => 'sppg_providers',
        ]);
    }

    public function adminSchool(string $schoolId): static
    {
        return $this->state(fn () => [
            'role'        => 'admin_school',
            'entity_id'   => $schoolId,
            'entity_type' => 'schools',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
