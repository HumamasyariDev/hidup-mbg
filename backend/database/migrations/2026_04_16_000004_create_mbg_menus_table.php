<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbg_menus', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('sppg_provider_id');
            $table->string('menu_name');
            $table->text('description')->nullable();
            $table->date('serve_date')->index();
            $table->enum('meal_type', ['breakfast', 'lunch', 'snack'])->index();
            $table->json('nutrition_data')->nullable()->comment('AI Nutrition Vision output');
            $table->string('photo_path')->nullable();
            $table->decimal('calories', 8, 2)->nullable();
            $table->decimal('protein_g', 8, 2)->nullable();
            $table->decimal('carbs_g', 8, 2)->nullable();
            $table->decimal('fat_g', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('sppg_provider_id')->references('id')->on('sppg_providers')->cascadeOnDelete();
            $table->unique(['sppg_provider_id', 'serve_date', 'meal_type'], 'uq_menu_per_provider_date_meal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbg_menus');
    }
};
