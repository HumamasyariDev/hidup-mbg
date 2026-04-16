<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MbgMenu;
use App\Models\SppgProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MbgMenu>
 */
final class MbgMenuFactory extends Factory
{
    protected $model = MbgMenu::class;

    public function definition(): array
    {
        $menus = [
            'Nasi Goreng Ayam', 'Nasi Kuning', 'Soto Ayam', 'Bubur Ayam',
            'Nasi Uduk Telur', 'Mie Goreng Sayur', 'Nasi Tim Ayam',
            'Sup Ikan', 'Nasi Rawon', 'Nasi Pecel',
        ];

        return [
            'id'               => Str::uuid()->toString(),
            'sppg_provider_id' => SppgProvider::factory(),
            'menu_name'        => fake()->randomElement($menus),
            'description'      => fake('id_ID')->sentence(10),
            'serve_date'       => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'meal_type'        => fake()->randomElement(['breakfast', 'lunch']),
            'nutrition_data'   => [
                'vitamins'  => ['A', 'B1', 'C'],
                'minerals'  => ['Fe', 'Ca'],
                'allergens' => fake()->randomElement([[], ['gluten'], ['dairy'], ['egg']]),
            ],
            'photo_path'       => null,
            'calories'         => fake()->randomFloat(2, 250, 700),
            'protein_g'        => fake()->randomFloat(2, 8, 35),
            'carbs_g'          => fake()->randomFloat(2, 30, 80),
            'fat_g'            => fake()->randomFloat(2, 5, 25),
        ];
    }
}
