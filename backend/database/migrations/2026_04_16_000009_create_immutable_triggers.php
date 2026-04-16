<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Database-Level Tamper Protection for Audit Ledger
 *
 * Creates MySQL BEFORE UPDATE and BEFORE DELETE triggers on audit_ledgers.
 * If ANY process (including direct DB access, raw SQL, or compromised ORM)
 * attempts to modify or delete a ledger row, the trigger SIGNALS an error
 * and aborts the operation.
 *
 * This is the LAST LINE OF DEFENSE — even if an attacker bypasses the
 * AppendOnly trait, the database itself refuses the mutation.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Block UPDATE on audit_ledgers
        DB::unprepared('
            CREATE TRIGGER trg_audit_ledgers_no_update
            BEFORE UPDATE ON audit_ledgers
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: audit_ledgers is immutable. UPDATE operations are forbidden.";
            END
        ');

        // Block DELETE on audit_ledgers
        DB::unprepared('
            CREATE TRIGGER trg_audit_ledgers_no_delete
            BEFORE DELETE ON audit_ledgers
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: audit_ledgers is immutable. DELETE operations are forbidden.";
            END
        ');

        // Block UPDATE on daily_dispatches
        DB::unprepared('
            CREATE TRIGGER trg_daily_dispatches_no_update
            BEFORE UPDATE ON daily_dispatches
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: daily_dispatches is append-only. UPDATE operations are forbidden.";
            END
        ');

        // Block DELETE on daily_dispatches
        DB::unprepared('
            CREATE TRIGGER trg_daily_dispatches_no_delete
            BEFORE DELETE ON daily_dispatches
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: daily_dispatches is append-only. DELETE operations are forbidden.";
            END
        ');

        // Block UPDATE on school_receipts
        DB::unprepared('
            CREATE TRIGGER trg_school_receipts_no_update
            BEFORE UPDATE ON school_receipts
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: school_receipts is append-only. UPDATE operations are forbidden.";
            END
        ');

        // Block DELETE on school_receipts
        DB::unprepared('
            CREATE TRIGGER trg_school_receipts_no_delete
            BEFORE DELETE ON school_receipts
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: school_receipts is append-only. DELETE operations are forbidden.";
            END
        ');

        // Block UPDATE on user_feedbacks
        DB::unprepared('
            CREATE TRIGGER trg_user_feedbacks_no_update
            BEFORE UPDATE ON user_feedbacks
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: user_feedbacks is append-only. UPDATE operations are forbidden.";
            END
        ');

        // Block DELETE on user_feedbacks
        DB::unprepared('
            CREATE TRIGGER trg_user_feedbacks_no_delete
            BEFORE DELETE ON user_feedbacks
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "SECURITY VIOLATION: user_feedbacks is append-only. DELETE operations are forbidden.";
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_ledgers_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_audit_ledgers_no_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_daily_dispatches_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_daily_dispatches_no_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_school_receipts_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_school_receipts_no_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_user_feedbacks_no_update');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_user_feedbacks_no_delete');
    }
};
