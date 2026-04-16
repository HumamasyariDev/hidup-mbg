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

        // Each SPPG provider serves 3 schools
        $providers->each(function (SppgProvider $provider): void {
            School::factory()
                ->count(3)
                ->create(['sppg_provider_id' => $provider->id]);
        });
    }
}
