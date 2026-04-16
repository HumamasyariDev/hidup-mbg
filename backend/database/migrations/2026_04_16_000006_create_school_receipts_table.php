<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_receipts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('daily_dispatch_id');
            $table->uuid('school_id');
            $table->date('receipt_date')->index();
            $table->unsignedInteger('quantity_received')->comment('Portions actually received');
            $table->unsignedInteger('quantity_distributed')->default(0)->comment('Portions distributed to students');
            $table->unsignedInteger('quantity_damaged')->default(0);
            $table->enum('condition', ['good', 'partial_damage', 'major_damage'])->default('good');
            $table->text('notes')->nullable();
            $table->string('photo_proof_path')->nullable();
            $table->uuid('reported_by_admin_id');
            $table->decimal('reporter_latitude', 10, 7)->comment('Captured lat at report time');
            $table->decimal('reporter_longitude', 10, 7)->comment('Captured lng at report time');
            $table->timestamp('created_at')->useCurrent();

            // Append-only: no updated_at, no soft deletes

            $table->foreign('daily_dispatch_id')->references('id')->on('daily_dispatches');
            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('reported_by_admin_id')->references('id')->on('admins');

            $table->unique(['daily_dispatch_id', 'school_id'], 'uq_receipt_per_dispatch_school');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_receipts');
    }
};
