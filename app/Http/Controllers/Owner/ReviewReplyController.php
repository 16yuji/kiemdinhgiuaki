<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\Request;

class ReviewReplyController extends Controller
{
    public function index()
    {
        $hotelIds = Hotel::where('owner_id', auth()->id())->pluck('id');

        $reviews = Review::with(['hotel', 'customer', 'booking', 'reply'])
            ->whereIn('hotel_id', $hotelIds)
            ->latest()
            ->paginate(10);

        return view('owner.reviews.index', compact('reviews'));
    }

    public function store(Request $request, Review $review)
    {
        $this->authorizeReview($review);

        if ($review->status !== 'visible') {
            return back()->with('error', 'Không thể phản hồi đánh giá đang bị ẩn.');
        }

        if ($review->reply()->exists()) {
            return back()->with('error', 'Đánh giá này đã có phản hồi.');
        }

        $data = $request->validate([
            'reply' => ['required', 'string', 'max:2000'],
        ], [
            'reply.required' => 'Vui lòng nhập nội dung phản hồi.',
        ]);

        ReviewReply::create([
            'review_id' => $review->id,
            'owner_id' => auth()->id(),
            'reply' => $data['reply'],
        ]);

        return redirect()
            ->route('owner.reviews.index')
            ->with('success', 'Phản hồi đánh giá thành công.');
    }

    private function authorizeReview(Review $review): void
    {
        if ((int) $review->hotel->owner_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền phản hồi đánh giá này.');
        }
    }
}