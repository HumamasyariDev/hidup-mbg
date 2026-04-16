<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_dispatches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('sppg_provider_id');
            $table->uuid('school_id');
            $table->uuid('mbg_menu_id');
            $table->date('dispatch_date')->index();
            $table->unsignedInteger('quantity_sent')->comment('Portions dispatched');
            $table->string('vehicle_plate', 20)->nullable();
            $table->string('driver_name')->nullable();
            $table->timestamp('dispatched_at');
            $table->string('photo_proof_path')->nullable();
            $table->uuid('reported_by_admin_id');
            $table->timestamp('created_at')->useCurrent();

            // NO updated_at — append-only
            // NO soft deletes — immutable

            $table->foreign('sppg_provider_id')->references('id')->on('sppg_providers');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('mbg_menu_id')->references('id')->on('mbg_menus');
            $table->foreign('reported_by_admin_id')->references('id')->on('admins');

            $table->index(['sppg_provider_id', 'dispatch_date']);
            $table->index(['school_id', 'dispatch_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_dispatches');
    }
};
