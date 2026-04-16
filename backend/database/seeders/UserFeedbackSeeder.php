<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MbgMenu;
use App\Models\School;
use App\Models\UserFeedback;
use Illuminate\Database\Seeder;

final class UserFeedbackSeeder extends Seeder
{
    public function run(): void
    {
        School::all()->each(function (School $school): void {
            $menus = MbgMenu::where('sppg_provider_id', $school->sppg_provider_id)->get();

            if ($menus->isEmpty()) {
                return;
            }

            // 5 anonymous feedbacks per school
            for ($i = 0; $i < 5; $i++) {
                UserFeedback::factory()->create([
                    'school_id'   => $school->id,
                    'mbg_menu_id' => $menus->random()->id,
                ]);
            }
        });
    }
}
