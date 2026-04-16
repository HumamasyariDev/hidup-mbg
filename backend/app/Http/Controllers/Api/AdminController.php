<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AdminController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Admin::class);

        $admins = Admin::query()
            ->when(request('role'), fn ($q, $role) => $q->byRole($role))
            ->when(request()->boolean('active_only'), fn ($q) => $q->active())
            ->orderBy('created_at', 'desc')
            ->paginate(request()->integer('per_page', 15));

        return AdminResource::collection($admins);
    }

    public function store(StoreAdminRequest $request): JsonResponse
    {
        $admin = Admin::create($request->validated());

        return (new AdminResource($admin))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Admin $admin): AdminResource
    {
        $this->authorize('view', $admin);

        return new AdminResource($admin);
    }

    public function update(UpdateAdminRequest $request, Admin $admin): AdminResource
    {
        $admin->update($request->validated());

        return new AdminResource($admin->fresh());
    }

    public function destroy(Admin $admin): JsonResponse
    {
        $this->authorize('delete', $admin);

        $admin->delete();

        return response()->json(['message' => 'Admin berhasil dihapus.'], 200);
    }
}
