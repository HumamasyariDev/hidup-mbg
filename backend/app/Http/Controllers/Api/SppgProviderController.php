<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSppgProviderRequest;
use App\Http\Requests\UpdateSppgProviderRequest;
use App\Http\Resources\SppgProviderResource;
use App\Models\SppgProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SppgProviderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SppgProvider::class);

        $providers = SppgProvider::query()
            ->withCount('schools')
            ->when(request()->boolean('active_only'), fn ($q) => $q->active())
            ->when(request('city'), fn ($q, $city) => $q->where('city', $city))
            ->orderBy('name')
            ->paginate(request()->integer('per_page', 15));

        return SppgProviderResource::collection($providers);
    }

    public function store(StoreSppgProviderRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['latitude', 'longitude']);
        $provider = SppgProvider::create($data);

        if ($request->has(['latitude', 'longitude'])) {
            SppgProvider::setCoordinate(
                $provider->id,
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
            );
        }

        return (new SppgProviderResource($provider->fresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(SppgProvider $sppgProvider): SppgProviderResource
    {
        $this->authorize('view', $sppgProvider);

        return new SppgProviderResource(
            $sppgProvider->loadCount('schools')
        );
    }

    public function update(UpdateSppgProviderRequest $request, SppgProvider $sppgProvider): SppgProviderResource
    {
        $data = $request->safe()->except(['latitude', 'longitude']);
        $sppgProvider->update($data);

        if ($request->has(['latitude', 'longitude'])) {
            SppgProvider::setCoordinate(
                $sppgProvider->id,
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
            );
        }

        return new SppgProviderResource($sppgProvider->fresh()->loadCount('schools'));
    }

    public function destroy(SppgProvider $sppgProvider): JsonResponse
    {
        $this->authorize('delete', $sppgProvider);

        $sppgProvider->delete();

        return response()->json(['message' => 'SPPG Provider berhasil dihapus.'], 200);
    }
}
