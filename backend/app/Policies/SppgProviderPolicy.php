<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\SppgProvider;

final class SppgProviderPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active;
    }

    public function view(Admin $admin, SppgProvider $provider): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        return $admin->isAdminSppg() && $admin->entity_id === $provider->id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }

    public function update(Admin $admin, SppgProvider $provider): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        return $admin->isAdminSppg() && $admin->entity_id === $provider->id;
    }

    public function delete(Admin $admin, SppgProvider $provider): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }
}
