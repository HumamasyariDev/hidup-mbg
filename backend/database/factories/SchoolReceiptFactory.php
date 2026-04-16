<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Models\DailyDispatch;
use App\Models\School;
use App\Models\SchoolReceipt;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SchoolReceipt>
 */
final class SchoolReceiptFactory extends Factory
{
    protected $model = SchoolReceipt::class;

    public function definition(): array
    {
        $received = fake()->numberBetween(50, 500);

        return [
            'id'                   => Str::uuid()->toString(),
            'daily_dispatch_id'    => DailyDispatch::factory(),
            'school_id'            => School::factory(),
            'receipt_date'         => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'quantity_received'    => $received,
            'quantity_distributed' => (int) ($received * fake()->randomFloat(2, 0.85, 1.0)),
            'quantity_damaged'     => fake()->numberBetween(0, 5),
            'condition'            => fake()->randomElement(['good', 'acceptable', 'poor']),
            'notes'                => fake()->optional(0.3)->sentence(),
            'photo_proof_path'     => null,
            'reported_by_admin_id' => Admin::factory(),
            'reporter_latitude'    => fake()->latitude(-8.5, -6.0),
            'reporter_longitude'   => fake()->longitude(106.0, 112.0),
        ];
    }
}
