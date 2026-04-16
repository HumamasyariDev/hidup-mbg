<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MbgMenu;
use App\Models\School;
use App\Models\UserFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserFeedback>
 */
final class UserFeedbackFactory extends Factory
{
    protected $model = UserFeedback::class;

    public function definition(): array
    {
        return [
            'id'                 => Str::uuid()->toString(),
            'school_id'          => School::factory(),
            'mbg_menu_id'        => MbgMenu::factory(),
            'feedback_date'      => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'zkp_identity_hash'  => hash('sha256', Str::uuid()->toString()),
            'zkp_proof'          => json_encode(['proof' => bin2hex(random_bytes(32))]),
            'rating'             => fake()->numberBetween(1, 5),
            'taste_rating'       => fake()->numberBetween(1, 5),
            'portion_rating'     => fake()->numberBetween(1, 5),
            'comment'            => fake('id_ID')->optional(0.7)->sentence(8),
            'photo_path'         => null,
            'reporter_latitude'  => fake()->latitude(-8.5, -6.0),
            'reporter_longitude' => fake()->longitude(106.0, 112.0),
        ];
    }
}
