<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\DailyDispatch;
use App\Models\SchoolReceipt;
use Illuminate\Database\Seeder;

final class SchoolReceiptSeeder extends Seeder
{
    public function run(): void
    {
        DailyDispatch::all()->each(function (DailyDispatch $dispatch): void {
            $adminSchool = Admin::where('entity_id', $dispatch->school_id)
                ->where('role', 'admin_school')
                ->first();

            if (!$adminSchool) {
                return;
            }

            SchoolReceipt::factory()->create([
                'daily_dispatch_id'    => $dispatch->id,
                'school_id'            => $dispatch->school_id,
                'quantity_received'    => $dispatch->quantity_sent,
                'reported_by_admin_id' => $adminSchool->id,
            ]);
        });
    }
}
