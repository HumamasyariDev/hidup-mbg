<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security Events Log — Persistent, tamper-resistant log of all security-related events.
 * Separate from the audit_ledgers (which tracks business data integrity).
 *
 * Stores: failed logins, rate limit hits, geofence violations, GPS anomalies,
 * file upload rejections, ZKP failures, trust score changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('event_type', 50)->index()->comment('login_failed, geofence_violation, gps_spoof, etc.');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->index();
            $table->string('ip_address', 45)->index();
            $table->string('user_agent')->nullable();
            $table->uuid('actor_id')->nullable()->comment('Admin UUID if known');
            $table->string('device_fingerprint', 64)->nullable()->index();
            $table->json('metadata')->nullable()->comment('Event-specific details');
            $table->timestamp('created_at')->useCurrent()->index();

            // Append-only, no updated_at
        });

        // Block mutation at DB level
        \Illuminate\Support\Facades\DB::unprepared('
            CREATE TRIGGER trg_security_events_no_update
            BEFORE UPDATE ON security_events
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: security_events is immutable.";
            END
        ');

        \Illuminate\Support\Facades\DB::unprepared('
            CREATE TRIGGER trg_security_events_no_delete
            BEFORE DELETE ON security_events
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: security_events is immutable.";
            END
        ');
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER IF EXISTS trg_security_events_no_update');
        \Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER IF EXISTS trg_security_events_no_delete');
        Schema::dropIfExists('security_events');
    }
};
