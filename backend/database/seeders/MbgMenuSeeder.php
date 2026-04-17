<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MbgMenu;
use App\Models\SppgProvider;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

final class MbgMenuSeeder extends Seeder
{
    public function run(): void
    {
        $mealTypes = ['breakfast', 'lunch'];

        SppgProvider::all()->each(function (SppgProvider $provider) use ($mealTypes): void {
            // 4 menus: 2 days x 2 meal types = unique combos
            $baseDate = Carbon::now()->addDays(1);

            foreach ([0, 1] as $dayOffset) {
                foreach ($mealTypes as $meal) {
                    MbgMenu::factory()->create([
                        'sppg_provider_id' => $provider->id,
                        'serve_date'       => $baseDate->copy()->addDays($dayOffset)->format('Y-m-d'),
                        'meal_type'        => $meal,
                    ]);
                }
            }
        });
    }
}
