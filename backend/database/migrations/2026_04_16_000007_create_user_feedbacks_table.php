<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feedbacks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('school_id');
            $table->uuid('mbg_menu_id');
            $table->date('feedback_date')->index();

            // ZKP anonymous identity — NO user_id stored
            $table->string('zkp_identity_hash', 64)->comment('SHA-256 of ZKP DID identifier');
            $table->string('zkp_proof', 512)->comment('ZKP proof token for verification');

            $table->unsignedTinyInteger('rating')->comment('1-5 scale');
            $table->enum('taste_rating', ['very_bad', 'bad', 'neutral', 'good', 'excellent'])->nullable();
            $table->enum('portion_rating', ['too_small', 'small', 'adequate', 'large', 'too_large'])->nullable();
            $table->text('comment')->nullable();
            $table->string('photo_path')->nullable();
            $table->decimal('reporter_latitude', 10, 7);
            $table->decimal('reporter_longitude', 10, 7);
            $table->timestamp('created_at')->useCurrent();

            // Append-only

            $table->foreign('school_id')->references('id')->on('schools');
            $table->foreign('mbg_menu_id')->references('id')->on('mbg_menus');

            $table->index(['school_id', 'feedback_date']);
            $table->index('zkp_identity_hash');

            // Prevent same ZKP identity from rating same menu twice
            $table->unique(['zkp_identity_hash', 'mbg_menu_id'], 'uq_one_feedback_per_identity_menu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feedbacks');
    }
};
