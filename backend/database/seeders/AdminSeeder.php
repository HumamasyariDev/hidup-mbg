<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\School;
use App\Models\SppgProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1 Super Admin (deterministic for dev login)
        Admin::factory()->superAdmin()->create([
            'name'     => 'Super Admin MBG',
            'email'    => 'superadmin@mbg.go.id',
            'password' => Hash::make('SuperAdmin#2026!'),
        ]);

        // 1 admin per SPPG provider
        SppgProvider::all()->each(function (SppgProvider $provider): void {
            Admin::factory()->adminSppg($provider->id)->create();
        });

        // 1 admin per school
        School::all()->each(function (School $school): void {
            Admin::factory()->adminSchool($school->id)->create();
        });
    }
}
