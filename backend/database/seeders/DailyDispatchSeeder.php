<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\DailyDispatch;
use App\Models\MbgMenu;
use App\Models\School;
use App\Models\SppgProvider;
use Illuminate\Database\Seeder;

final class DailyDispatchSeeder extends Seeder
{
    public function run(): void
    {
        SppgProvider::with(['schools', 'menus'])->get()->each(function (SppgProvider $provider): void {
            $adminSppg = Admin::where('entity_id', $provider->id)
                ->where('role', 'admin_sppg')
                ->first();

            if (!$adminSppg || $provider->schools->isEmpty() || $provider->menus->isEmpty()) {
                return;
            }

            $provider->schools->each(function (School $school) use ($provider, $adminSppg): void {
                // 3 dispatches per school
                for ($i = 0; $i < 3; $i++) {
                    DailyDispatch::factory()->create([
                        'sppg_provider_id'     => $provider->id,
                        'school_id'            => $school->id,
                        'mbg_menu_id'          => $provider->menus->random()->id,
                        'reported_by_admin_id' => $adminSppg->id,
                    ]);
                }
            });
        });
    }
}
