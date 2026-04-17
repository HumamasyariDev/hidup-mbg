<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SppgProvider;
use Illuminate\Database\Seeder;

final class SppgProviderSeeder extends Seeder
{
    public function run(): void
    {
        // Indonesian city coordinates (lat, lng)
        $coordinates = [
            [-6.2088, 106.8456],  // Jakarta
            [-6.9175, 107.6191],  // Bandung
            [-7.2575, 112.7521],  // Surabaya
            [-7.7956, 110.3695],  // Yogyakarta
            [-6.9932, 110.4203],  // Semarang
        ];

        for ($i = 0; $i < 5; $i++) {
            $provider = SppgProvider::factory()->create();
            SppgProvider::setCoordinate($provider->id, $coordinates[$i][0], $coordinates[$i][1]);
        }
    }
}
