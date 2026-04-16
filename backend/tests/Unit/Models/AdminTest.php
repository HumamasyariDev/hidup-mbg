<?php

declare(strict_types=1);

use App\Models\Admin;

test('Admin isSuperAdmin returns true for super_admin role', function (): void {
    $admin = new Admin(['role' => 'super_admin']);
    expect($admin->isSuperAdmin())->toBeTrue();
    expect($admin->isAdminSppg())->toBeFalse();
    expect($admin->isAdminSchool())->toBeFalse();
});

test('Admin isAdminSppg returns true for admin_sppg role', function (): void {
    $admin = new Admin(['role' => 'admin_sppg']);
    expect($admin->isAdminSppg())->toBeTrue();
    expect($admin->isSuperAdmin())->toBeFalse();
});

test('Admin isAdminSchool returns true for admin_school role', function (): void {
    $admin = new Admin(['role' => 'admin_school']);
    expect($admin->isAdminSchool())->toBeTrue();
    expect($admin->isSuperAdmin())->toBeFalse();
});

test('Admin uses UUID as primary key', function (): void {
    $admin = new Admin();
    expect($admin->getIncrementing())->toBeFalse();
    expect($admin->getKeyType())->toBe('string');
});

test('Admin hidden attributes include password', function (): void {
    $admin = new Admin();
    expect($admin->getHidden())->toContain('password');
    expect($admin->getHidden())->toContain('remember_token');
});
