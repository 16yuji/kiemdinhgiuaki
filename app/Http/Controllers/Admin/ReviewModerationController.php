<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewModerationController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with(['hotel', 'customer', 'booking', 'reply.owner', 'hiddenBy'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->keyword, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('comment', 'like', "%{$keyword}%")
                        ->orWhereHas('hotel', function ($hotelQuery) use ($keyword) {
                            $hotelQuery->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('customer', function ($customerQuery) use ($keyword) {
                            $customerQuery->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function hide(Request $request, Review $review)
    {
        $data = $request->validate([
            'hidden_reason' => ['required', 'string', 'max:1000'],
        ], [
            'hidden_reason.required' => 'Vui lòng nhập lý do ẩn đánh giá.',
        ]);

        if ($review->status === 'hidden') {
            return back()->with('error', 'Đánh giá này đã bị ẩn trước đó.');
        }

        DB::transaction(function () use ($review, $data) {
            $review->update([
                'status' => 'hidden',
                'hidden_by' => auth()->id(),
                'hidden_reason' => $data['hidden_reason'],
                'hidden_at' => now(),
            ]);

            $this->refreshHotelRating($review->hotel);
        });

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Ẩn đánh giá thành công.');
    }

    public function restore(Review $review)
    {
        if ($review->status === 'visible') {
            return back()->with('error', 'Đánh giá này đang hiển thị.');
        }

        DB::transaction(function () use ($review) {
            $review->update([
                'status' => 'visible',
                'hidden_by' => null,
                'hidden_reason' => null,
                'hidden_at' => null,
            ]);

            $this->refreshHotelRating($review->hotel);
        });

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Khôi phục đánh giá thành công.');
    }

    private function refreshHotelRating(Hotel $hotel): void
    {
        $visibleReviews = $hotel->reviews()
            ->where('status', 'visible');

        $hotel->update([
            'average_rating' => round((float) $visibleReviews->avg('rating'), 2),
            'review_count' => $visibleReviews->count(),
        ]);
    }
}