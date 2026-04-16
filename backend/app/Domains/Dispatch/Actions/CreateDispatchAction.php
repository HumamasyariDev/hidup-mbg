<?php

declare(strict_types=1);

namespace App\Domains\Dispatch\Actions;

use App\Domains\Ledger\Services\LedgerService;
use App\Models\DailyDispatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Single-action controller for creating a daily dispatch report.
 * Invoked after GeofencingMiddleware + Sanctum auth.
 */
final class CreateDispatchAction
{
    public function __construct(
        private readonly LedgerService $ledger,
    ) {}

    public function __invoke(Request $request, string $sppg_provider_id): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'school_id' => ['required', 'uuid', 'exists:schools,id'],
            'mbg_menu_id' => ['required', 'uuid', 'exists:mbg_menus,id'],
            'dispatch_date' => ['required', 'date', 'before_or_equal:today'],
            'quantity_sent' => ['required', 'integer', 'min:1'],
            'vehicle_plate' => ['nullable', 'string', 'max:20'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'dispatched_at' => ['required', 'date'],
            'photo_proof_path' => ['nullable', 'string'],
        ])->validate();

        $dispatch = DailyDispatch::create([
            ...$validated,
            'sppg_provider_id' => $sppg_provider_id,
            'reported_by_admin_id' => $request->user()->id,
        ]);

        // Record in immutable audit ledger
        $this->ledger->record(
            entity: $dispatch,
            request: $request,
            actorId: $request->user()->id,
            actorType: 'admin',
        );

        return response()->json([
            'message' => 'Laporan pengiriman berhasil dicatat.',
            'data' => $dispatch,
        ], 201);
    }
}
