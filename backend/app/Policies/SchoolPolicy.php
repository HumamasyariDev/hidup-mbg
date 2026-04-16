<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Admin;
use App\Models\School;

final class SchoolPolicy
{
    public function viewAny(Admin $admin): bool
    {
        return $admin->is_active;
    }

    public function view(Admin $admin, School $school): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        if ($admin->isAdminSppg()) return $admin->entity_id === $school->sppg_provider_id;
        return $admin->isAdminSchool() && $admin->entity_id === $school->id;
    }

    public function create(Admin $admin): bool
    {
        return $admin->is_active && ($admin->isSuperAdmin() || $admin->isAdminSppg());
    }

    public function update(Admin $admin, School $school): bool
    {
        if (!$admin->is_active) return false;
        if ($admin->isSuperAdmin()) return true;
        if ($admin->isAdminSppg()) return $admin->entity_id === $school->sppg_provider_id;
        return $admin->isAdminSchool() && $admin->entity_id === $school->id;
    }

    public function delete(Admin $admin, School $school): bool
    {
        return $admin->is_active && $admin->isSuperAdmin();
    }
}
