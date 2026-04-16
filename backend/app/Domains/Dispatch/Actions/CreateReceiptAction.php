<?php

declare(strict_types=1);

namespace App\Domains\Dispatch\Actions;

use App\Domains\Ledger\Services\LedgerService;
use App\Models\SchoolReceipt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Single-action controller for recording school receipt of meals.
 * Invoked after GeofencingMiddleware + Sanctum auth.
 */
final class CreateReceiptAction
{
    public function __construct(
        private readonly LedgerService $ledger,
    ) {}

    public function __invoke(Request $request, string $school_id): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'daily_dispatch_id' => ['required', 'uuid', 'exists:daily_dispatches,id'],
            'receipt_date' => ['required', 'date', 'before_or_equal:today'],
            'quantity_received' => ['required', 'integer', 'min:0'],
            'quantity_distributed' => ['nullable', 'integer', 'min:0'],
            'quantity_damaged' => ['nullable', 'integer', 'min:0'],
            'condition' => ['required', 'in:good,partial_damage,major_damage'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'photo_proof_path' => ['nullable', 'string'],
        ])->validate();

        $receipt = SchoolReceipt::create([
            ...$validated,
            'school_id' => $school_id,
            'reported_by_admin_id' => $request->user()->id,
            'reporter_latitude' => $request->input('validated_latitude'),
            'reporter_longitude' => $request->input('validated_longitude'),
        ]);

        $this->ledger->record(
            entity: $receipt,
            request: $request,
            actorId: $request->user()->id,
            actorType: 'admin',
        );

        return response()->json([
            'message' => 'Laporan penerimaan berhasil dicatat.',
            'data' => $receipt,
        ], 201);
    }
}
