<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('npsn', 20)->unique()->comment('Nomor Pokok Sekolah Nasional');
            $table->text('address');
            $table->string('city')->index();
            $table->string('province')->index();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('level', ['SD', 'SMP', 'SMA', 'SMK'])->index();
            $table->unsignedInteger('student_count')->default(0);
            $table->unsignedInteger('geofence_radius_meters')->default(100)->comment('Allowed radius for geofencing');
            $table->uuid('sppg_provider_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('sppg_provider_id')->references('id')->on('sppg_providers')->nullOnDelete();
        });

        DB::statement('ALTER TABLE schools ADD COLUMN coordinate POINT NOT NULL SRID 4326 AFTER province');
        DB::statement('CREATE SPATIAL INDEX idx_schools_coordinate ON schools (coordinate)');
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
