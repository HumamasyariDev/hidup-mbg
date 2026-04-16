<?php

declare(strict_types=1);

use App\Models\MbgMenu;
use App\Models\SppgProvider;
use App\Models\School;
use App\Models\DailyDispatch;
use App\Models\SchoolReceipt;
use App\Models\UserFeedback;
use App\Models\AuditLedger;
use App\Models\SecurityEvent;

test('SppgProvider uses UUID and is not incrementing', function (): void {
    $model = new SppgProvider();
    expect($model->getIncrementing())->toBeFalse();
    expect($model->getKeyType())->toBe('string');
});

test('SppgProvider casts license_number as encrypted', function (): void {
    $model = new SppgProvider();
    $casts = $model->getCasts();
    expect($casts['license_number'])->toBe('encrypted');
    expect($casts['is_active'])->toBe('boolean');
    expect($casts['capacity_per_day'])->toBe('integer');
});

test('School casts are correct', function (): void {
    $model = new School();
    $casts = $model->getCasts();
    expect($casts['student_count'])->toBe('integer');
    expect($casts['geofence_radius_meters'])->toBe('integer');
    expect($casts['is_active'])->toBe('boolean');
});

test('MbgMenu casts nutrition_data as array', function (): void {
    $model = new MbgMenu();
    $casts = $model->getCasts();
    expect($casts['nutrition_data'])->toBe('array');
    expect($casts['serve_date'])->toBe('date');
});

test('DailyDispatch has no UPDATED_AT', function (): void {
    expect(DailyDispatch::UPDATED_AT)->toBeNull();
});

test('SchoolReceipt has no UPDATED_AT', function (): void {
    expect(SchoolReceipt::UPDATED_AT)->toBeNull();
});

test('UserFeedback uses custom table name', function (): void {
    $model = new UserFeedback();
    expect($model->getTable())->toBe('user_feedbacks');
});

test('AuditLedger has no UPDATED_AT', function (): void {
    expect(AuditLedger::UPDATED_AT)->toBeNull();
});

test('SecurityEvent has no UPDATED_AT', function (): void {
    expect(SecurityEvent::UPDATED_AT)->toBeNull();
});
