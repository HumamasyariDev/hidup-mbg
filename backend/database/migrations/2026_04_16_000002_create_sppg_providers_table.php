<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sppg_providers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('license_number')->unique();
            $table->text('address');
            $table->string('city')->index();
            $table->string('province')->index();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('capacity_per_day')->default(0)->comment('Max portions per day');
            $table->timestamps();
        });

        // Add spatial POINT column — nullable for seeding compatibility
        DB::statement('ALTER TABLE sppg_providers ADD COLUMN coordinate POINT NULL AFTER province');
    }

    public function down(): void
    {
        Schema::dropIfExists('sppg_providers');
    }
};
