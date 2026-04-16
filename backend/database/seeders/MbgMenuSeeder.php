<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MbgMenu;
use App\Models\SppgProvider;
use Illuminate\Database\Seeder;

final class MbgMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 4 menus per SPPG provider
        SppgProvider::all()->each(function (SppgProvider $provider): void {
            MbgMenu::factory()
                ->count(4)
                ->create(['sppg_provider_id' => $provider->id]);
        });
    }
}
