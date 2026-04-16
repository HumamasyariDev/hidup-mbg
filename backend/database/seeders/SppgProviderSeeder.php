<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SppgProvider;
use Illuminate\Database\Seeder;

final class SppgProviderSeeder extends Seeder
{
    public function run(): void
    {
        // 5 active SPPG providers across Java
        SppgProvider::factory()->count(5)->create();
    }
}
