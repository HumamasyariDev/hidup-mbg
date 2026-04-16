<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyDispatchResource;
use App\Http\Resources\SchoolReceiptResource;
use App\Http\Resources\UserFeedbackResource;
use App\Models\DailyDispatch;
use App\Models\SchoolReceipt;
use App\Models\UserFeedback;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only controllers for transactional (append-only) tables.
 */
final class ReportController extends Controller
{
    public function dispatches(): AnonymousResourceCollection
    {
        $user = request()->user();

        $dispatches = DailyDispatch::query()
            ->with(['sppgProvider', 'school', 'menu'])
            ->when($user->isAdminSppg(), fn ($q) => $q->where('sppg_provider_id', $user->entity_id))
            ->when($user->isAdminSchool(), fn ($q) => $q->where('school_id', $user->entity_id))
            ->when(request('dispatch_date'), fn ($q, $d) => $q->where('dispatch_date', $d))
            ->orderBy('created_at', 'desc')
            ->paginate(request()->integer('per_page', 15));

        return DailyDispatchResource::collection($dispatches);
    }

    public function receipts(): AnonymousResourceCollection
    {
        $user = request()->user();

        $receipts = SchoolReceipt::query()
            ->with(['school', 'dailyDispatch'])
            ->when($user->isAdminSchool(), fn ($q) => $q->where('school_id', $user->entity_id))
            ->when($user->isAdminSppg(), fn ($q) => $q->whereHas('dailyDispatch', fn ($dq) => $dq->where('sppg_provider_id', $user->entity_id)))
            ->orderBy('created_at', 'desc')
            ->paginate(request()->integer('per_page', 15));

        return SchoolReceiptResource::collection($receipts);
    }

    public function feedbacks(): AnonymousResourceCollection
    {
        $feedbacks = UserFeedback::query()
            ->with(['school', 'menu'])
            ->when(request('school_id'), fn ($q, $id) => $q->where('school_id', $id))
            ->when(request('rating'), fn ($q, $r) => $q->where('rating', $r))
            ->orderBy('created_at', 'desc')
            ->paginate(request()->integer('per_page', 15));

        return UserFeedbackResource::collection($feedbacks);
    }
}
