<?php

declare(strict_types=1);

namespace App\Domains\Feedback\Actions;

use App\Domains\Ledger\Services\LedgerService;
use App\Models\UserFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Single-action controller for anonymous student feedback.
 * Invoked after ZkpAuthMiddleware + GeofencingMiddleware.
 *
 * No user_id is stored — only the ZKP identity hash.
 */
final class CreateFeedbackAction
{
    public function __construct(
        private readonly LedgerService $ledger,
    ) {}

    public function __invoke(Request $request, string $school_id): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'mbg_menu_id' => ['required', 'uuid', 'exists:mbg_menus,id'],
            'feedback_date' => ['required', 'date', 'before_or_equal:today'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'taste_rating' => ['nullable', 'in:very_bad,bad,neutral,good,excellent'],
            'portion_rating' => ['nullable', 'in:too_small,small,adequate,large,too_large'],
            'comment' => ['nullable', 'string', 'max:500'],
            'photo_path' => ['nullable', 'string'],
        ])->validate();

        $feedback = UserFeedback::create([
            ...$validated,
            'school_id' => $school_id,
            'zkp_identity_hash' => $request->input('zkp_identity_hash'),
            'zkp_proof' => $request->input('zkp_proof'),
            'reporter_latitude' => $request->input('validated_latitude'),
            'reporter_longitude' => $request->input('validated_longitude'),
        ]);

        $this->ledger->record(
            entity: $feedback,
            request: $request,
            actorId: null,
            actorType: 'zkp_anonymous',
        );

        return response()->json([
            'message' => 'Feedback berhasil dikirim secara anonim.',
            'data' => [
                'id' => $feedback->id,
                'rating' => $feedback->rating,
            ],
        ], 201);
    }
}
