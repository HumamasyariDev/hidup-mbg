<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\DailyDispatch;
use App\Models\MbgMenu;
use App\Models\School;
use App\Models\SppgProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DailyDispatch>
 */
final class DailyDispatchFactory extends Factory
{
    protected $model = DailyDispatch::class;

    public function definition(): array
    {
        return [
            'id'                   => Str::uuid()->toString(),
            'sppg_provider_id'     => SppgProvider::factory(),
            'school_id'            => School::factory(),
            'mbg_menu_id'          => MbgMenu::factory(),
            'dispatch_date'        => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'quantity_sent'        => fake()->numberBetween(50, 500),
            'vehicle_plate'       => fake()->regexify('[A-Z]{1,2} [0-9]{1,4} [A-Z]{1,3}'),
            'driver_name'          => fake('id_ID')->name('male'),
            'dispatched_at'        => now(),
            'photo_proof_path'     => null,
            'reported_by_admin_id' => Admin::factory(),
        ];
    }
}
