<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\School;
use App\Models\SppgProvider;
use Illuminate\Database\Seeder;

final class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $providers = SppgProvider::all();

        $providers->each(function (SppgProvider $provider): void {
            for ($i = 0; $i < 3; $i++) {
                $school = School::factory()->create(['sppg_provider_id' => $provider->id]);

                // Random coordinate near Indonesia (-6 to -8 lat, 106 to 113 lng)
                $lat = fake()->latitude(-8.0, -6.0);
                $lng = fake()->longitude(106.0, 113.0);
                School::setCoordinate($school->id, $lat, $lng);
            }
        });
    }
}
