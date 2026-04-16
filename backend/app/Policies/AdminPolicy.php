<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;

final class AdminPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }

    public function view(Admin $admin, Admin $target): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        return $admin->id === $target->id; // can view self
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }

    public function update(Admin $admin, Admin $target): bool
    {
        if (!$admin->is_active) return false;
        return $admin->isSuperAdmin();
    }

    public function delete(Admin $admin, Admin $target): bool
    {
        if (!$admin->is_active) return false;
        return $admin->isSuperAdmin() && $admin->id !== $target->id; // can't delete self
    }
}
