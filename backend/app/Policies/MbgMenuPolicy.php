<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\MbgMenu;

final class MbgMenuPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active;
    }

    public function view(Admin $admin, MbgMenu $menu): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        return $admin->isAdminSppg() && $admin->entity_id === $menu->sppg_provider_id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && ($admin->isSuperAdmin() || $admin->isAdminSppg());
    }

    public function update(Admin $admin, MbgMenu $menu): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        return $admin->isAdminSppg() && $admin->entity_id === $menu->sppg_provider_id;
    }

    public function delete(Admin $admin, MbgMenu $menu): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        return $admin->isAdminSppg() && $admin->entity_id === $menu->sppg_provider_id;
    }
}
