<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SchoolController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', School::class);

        $user = request()->user();

        $schools = School::query()
            ->with('sppgProvider')
            ->when($user->isAdminSppg(), fn ($q) => $q->where('sppg_provider_id', $user->entity_id))
            ->when($user->isAdminSchool(), fn ($q) => $q->where('id', $user->entity_id))
            ->when(request()->boolean('active_only'), fn ($q) => $q->active())
            ->when(request('level'), fn ($q, $level) => $q->where('level', $level))
            ->when(request('city'), fn ($q, $city) => $q->where('city', $city))
            ->orderBy('name')
            ->paginate(request()->integer('per_page', 15));

        return SchoolResource::collection($schools);
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['latitude', 'longitude']);
        $school = School::create($data);

        if ($request->has(['latitude', 'longitude'])) {
            School::setCoordinate(
                $school->id,
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
            );
        }

        return (new SchoolResource($school->fresh()->load('sppgProvider')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(School $school): SchoolResource
    {
        $this->authorize('view', $school);

        return new SchoolResource($school->load('sppgProvider'));
    }

    public function update(UpdateSchoolRequest $request, School $school): SchoolResource
    {
        $data = $request->safe()->except(['latitude', 'longitude']);
        $school->update($data);

        if ($request->has(['latitude', 'longitude'])) {
            School::setCoordinate(
                $school->id,
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
            );
        }

        return new SchoolResource($school->fresh()->load('sppgProvider'));
    }

    public function destroy(School $school): JsonResponse
    {
        $this->authorize('delete', $school);

        $school->delete();

        return response()->json(['message' => 'Sekolah berhasil dihapus.'], 200);
    }
}
