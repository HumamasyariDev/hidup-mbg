<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_ledgers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('sequence_number')->unique()->comment('Monotonic sequence for chain integrity');
            $table->string('entity_type', 100)->comment('daily_dispatches | school_receipts | user_feedbacks');
            $table->uuid('entity_id');
            $table->string('action', 20)->default('create')->comment('create only — append-only ledger');
            $table->json('payload_snapshot')->comment('Full JSON snapshot of the record at creation');
            $table->string('current_hash', 64)->comment('SHA-256 of payload_snapshot');
            $table->string('previous_hash', 64)->comment('Hash of the previous ledger entry for chain validation');
            $table->uuid('actor_id')->nullable()->comment('Admin UUID or null for anonymous ZKP');
            $table->string('actor_type', 50)->nullable()->comment('admin | zkp_anonymous');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Append-only: no updated_at, no deletes

            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
            $table->index('current_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_ledgers');
    }
};
