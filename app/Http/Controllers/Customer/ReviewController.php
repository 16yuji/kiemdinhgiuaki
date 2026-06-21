<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Services\Ai\ReviewSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function create(Booking $booking)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'completed') {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Bạn chỉ có thể đánh giá sau khi đơn đã hoàn tất.');
        }

        if ($booking->review()->exists()) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Bạn đã đánh giá đơn đặt phòng này.');
        }

        $booking->load(['hotel', 'roomTypes.roomType']);

        return view('customer.reviews.create', compact('booking'));
    }

    public function store(Request $request, Booking $booking, ReviewSummaryService $reviewSummaryService)
    {
        $this->authorizeBooking($booking);

        if ($booking->status !== 'completed') {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Bạn chỉ có thể đánh giá sau khi đơn đã hoàn tất.');
        }

        if ($booking->review()->exists()) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Bạn đã đánh giá đơn đặt phòng này.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.min' => 'Số sao đánh giá không hợp lệ.',
            'rating.max' => 'Số sao đánh giá không hợp lệ.',
        ]);

        $hotel = null;

        DB::transaction(function () use ($booking, $data, &$hotel) {
            Review::create([
                'booking_id' => $booking->id,
                'hotel_id' => $booking->hotel_id,
                'customer_id' => auth()->id(),
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'status' => 'visible',
            ]);

            $hotel = $booking->hotel;

            $visibleReviews = $hotel->reviews()
                ->where('status', 'visible');

            $hotel->update([
                'average_rating' => round((float) $visibleReviews->avg('rating'), 2),
                'review_count' => $visibleReviews->count(),
            ]);
        });

        if ($hotel) {
            $reviewSummaryService->refreshIfEligible($hotel);
        }

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->with('success', 'Cảm ơn bạn đã đánh giá dịch vụ.');
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ((int) $booking->customer_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền đánh giá đơn này.');
        }
    }
}
