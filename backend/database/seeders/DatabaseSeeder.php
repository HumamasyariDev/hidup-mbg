<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\DailyDispatch;
use App\Models\MbgMenu;
use App\Models\School;
use App\Models\SchoolReceipt;
use App\Models\SppgProvider;
use App\Models\UserFeedback;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            SppgProviderSeeder::class,
            SchoolSeeder::class,
            AdminSeeder::class,
            MbgMenuSeeder::class,
            DailyDispatchSeeder::class,
            SchoolReceiptSeeder::class,
            UserFeedbackSeeder::class,
        ]);
    }
}
