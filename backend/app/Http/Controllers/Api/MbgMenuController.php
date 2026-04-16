<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMbgMenuRequest;
use App\Http\Requests\UpdateMbgMenuRequest;
use App\Http\Resources\MbgMenuResource;
use App\Models\MbgMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MbgMenuController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MbgMenu::class);

        $user = request()->user();

        $menus = MbgMenu::query()
            ->with('sppgProvider')
            ->when($user->isAdminSppg(), fn ($q) => $q->where('sppg_provider_id', $user->entity_id))
            ->when(request('meal_type'), fn ($q, $type) => $q->where('meal_type', $type))
            ->when(request('serve_date'), fn ($q, $date) => $q->where('serve_date', $date))
            ->when(request('sppg_provider_id'), fn ($q, $id) => $q->where('sppg_provider_id', $id))
            ->orderBy('serve_date', 'desc')
            ->paginate(request()->integer('per_page', 15));

        return MbgMenuResource::collection($menus);
    }

    public function store(StoreMbgMenuRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['photo']);

        // If SPPG admin, force their own provider ID
        if ($request->user()->isAdminSppg()) {
            $data['sppg_provider_id'] = $request->user()->entity_id;
        }

        $menu = MbgMenu::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('menus', 'public');
            $menu->update(['photo_path' => $path]);
        }

        return (new MbgMenuResource($menu->fresh()->load('sppgProvider')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(MbgMenu $mbgMenu): MbgMenuResource
    {
        $this->authorize('view', $mbgMenu);

        return new MbgMenuResource($mbgMenu->load('sppgProvider'));
    }

    public function update(UpdateMbgMenuRequest $request, MbgMenu $mbgMenu): MbgMenuResource
    {
        $data = $request->safe()->except(['photo']);
        $mbgMenu->update($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('menus', 'public');
            $mbgMenu->update(['photo_path' => $path]);
        }

        return new MbgMenuResource($mbgMenu->fresh()->load('sppgProvider'));
    }

    public function destroy(MbgMenu $mbgMenu): JsonResponse
    {
        $this->authorize('delete', $mbgMenu);

        $mbgMenu->delete();

        return response()->json(['message' => 'Menu berhasil dihapus.'], 200);
    }
}
