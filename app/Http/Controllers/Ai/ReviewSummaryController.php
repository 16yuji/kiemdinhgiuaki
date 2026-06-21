<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\Ai\ReviewSummaryService;

class ReviewSummaryController extends Controller
{
    public function refresh(Hotel $hotel, ReviewSummaryService $summaryService)
    {
        $user = auth()->user();

        if (!$user || !in_array($user->role, ['admin', 'owner'], true)) {
            abort(403);
        }

        if ($user->role === 'owner' && (int) $hotel->owner_id !== (int) $user->id) {
            abort(403);
        }

        $summary = $summaryService->refreshIfEligible($hotel, true);

        if (!$summary) {
            return response()->json([
                'message' => 'Chưa đủ đánh giá công khai để tạo tóm tắt.',
            ], 422);
        }

        return response()->json([
            'summary' => $summary->summary,
            'pros' => $summary->pros ?: [],
            'cons' => $summary->cons ?: [],
            'review_count' => $summary->review_count,
            'generated_by' => $summary->generated_by,
        ]);
    }
}
