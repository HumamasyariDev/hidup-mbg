<?php

declare(strict_types=1);

use App\Policies\AdminPolicy;
use App\Policies\SppgProviderPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\MbgMenuPolicy;
use App\Models\Admin;
use App\Models\SppgProvider;
use App\Models\School;
use App\Models\MbgMenu;

// --- AdminPolicy ---

test('AdminPolicy: super admin can viewAny', function (): void {
    $admin = new Admin(['role' => 'super_admin', 'is_active' => true]);
    $policy = new AdminPolicy();
    expect($policy->viewAny($admin))->toBeTrue();
});

test('AdminPolicy: non-super admin cannot viewAny', function (): void {
    $admin = new Admin(['role' => 'admin_sppg', 'is_active' => true]);
    $policy = new AdminPolicy();
    expect($policy->viewAny($admin))->toBeFalse();
});

test('AdminPolicy: inactive admin cannot viewAny', function (): void {
    $admin = new Admin(['role' => 'super_admin', 'is_active' => false]);
    $policy = new AdminPolicy();
    expect($policy->viewAny($admin))->toBeFalse();
});

test('AdminPolicy: super admin can create', function (): void {
    $admin = new Admin(['role' => 'super_admin', 'is_active' => true]);
    $policy = new AdminPolicy();
    expect($policy->create($admin))->toBeTrue();
});

test('AdminPolicy: super admin cannot delete self', function (): void {
    $admin = new Admin(['role' => 'super_admin', 'is_active' => true]);
    $admin->id = 'uuid-1';
    $target = new Admin();
    $target->id = 'uuid-1';
    $policy = new AdminPolicy();
    expect($policy->delete($admin, $target))->toBeFalse();
});

test('AdminPolicy: super admin can delete other admin', function (): void {
    $admin = new Admin(['role' => 'super_admin', 'is_active' => true]);
    $admin->id = 'uuid-1';
    $target = new Admin();
    $target->id = 'uuid-2';
    $policy = new AdminPolicy();
    expect($policy->delete($admin, $target))->toBeTrue();
});

// --- SppgProviderPolicy ---

test('SppgProviderPolicy: super admin can do everything', function (): void {
    $admin = new Admin(['role' => 'super_admin', 'is_active' => true]);
    $provider = new SppgProvider();
    $provider->id = 'p-1';
    $policy = new SppgProviderPolicy();

    expect($policy->viewAny($admin))->toBeTrue();
    expect($policy->view($admin, $provider))->toBeTrue();
    expect($policy->create($admin))->toBeTrue();
    expect($policy->update($admin, $provider))->toBeTrue();
    expect($policy->delete($admin, $provider))->toBeTrue();
});

test('SppgProviderPolicy: admin_sppg can only view/update own provider', function (): void {
    $admin = new Admin(['role' => 'admin_sppg', 'is_active' => true, 'entity_id' => 'p-1']);
    $own = new SppgProvider();
    $own->id = 'p-1';
    $other = new SppgProvider();
    $other->id = 'p-2';
    $policy = new SppgProviderPolicy();

    expect($policy->view($admin, $own))->toBeTrue();
    expect($policy->view($admin, $other))->toBeFalse();
    expect($policy->update($admin, $own))->toBeTrue();
    expect($policy->update($admin, $other))->toBeFalse();
    expect($policy->create($admin))->toBeFalse();
    expect($policy->delete($admin, $own))->toBeFalse();
});

// --- SchoolPolicy ---

test('SchoolPolicy: admin_sppg can view schools of own provider', function (): void {
    $admin = new Admin(['role' => 'admin_sppg', 'is_active' => true, 'entity_id' => 'p-1']);
    $school = new School(['sppg_provider_id' => 'p-1']);
    $school->id = 's-1';
    $otherSchool = new School(['sppg_provider_id' => 'p-2']);
    $otherSchool->id = 's-2';
    $policy = new SchoolPolicy();

    expect($policy->view($admin, $school))->toBeTrue();
    expect($policy->view($admin, $otherSchool))->toBeFalse();
});

test('SchoolPolicy: admin_school can only view own school', function (): void {
    $admin = new Admin(['role' => 'admin_school', 'is_active' => true, 'entity_id' => 's-1']);
    $own = new School(['sppg_provider_id' => 'p-1']);
    $own->id = 's-1';
    $other = new School(['sppg_provider_id' => 'p-1']);
    $other->id = 's-2';
    $policy = new SchoolPolicy();

    expect($policy->view($admin, $own))->toBeTrue();
    expect($policy->view($admin, $other))->toBeFalse();
});

// --- MbgMenuPolicy ---

test('MbgMenuPolicy: admin_sppg can CRUD own menus', function (): void {
    $admin = new Admin(['role' => 'admin_sppg', 'is_active' => true, 'entity_id' => 'p-1']);
    $menu = new MbgMenu(['sppg_provider_id' => 'p-1']);
    $otherMenu = new MbgMenu(['sppg_provider_id' => 'p-2']);
    $policy = new MbgMenuPolicy();

    expect($policy->create($admin))->toBeTrue();
    expect($policy->view($admin, $menu))->toBeTrue();
    expect($policy->view($admin, $otherMenu))->toBeFalse();
    expect($policy->update($admin, $menu))->toBeTrue();
    expect($policy->delete($admin, $menu))->toBeTrue();
    expect($policy->delete($admin, $otherMenu))->toBeFalse();
});
