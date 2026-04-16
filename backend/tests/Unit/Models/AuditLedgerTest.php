<?php

declare(strict_types=1);

use App\Models\AuditLedger;

test('AuditLedger verifyHash returns true for valid hash', function (): void {
    $payload = ['entity' => 'test', 'action' => 'create'];
    $hash = hash('sha256', json_encode($payload));

    $ledger = new AuditLedger();
    $ledger->payload_snapshot = $payload;
    $ledger->current_hash = $hash;

    expect($ledger->verifyHash())->toBeTrue();
});

test('AuditLedger verifyHash returns false for tampered data', function (): void {
    $payload = ['entity' => 'test', 'action' => 'create'];
    $hash = hash('sha256', json_encode($payload));

    $ledger = new AuditLedger();
    $ledger->payload_snapshot = ['entity' => 'TAMPERED', 'action' => 'create'];
    $ledger->current_hash = $hash;

    expect($ledger->verifyHash())->toBeFalse();
});
