<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Mail\BookingMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', 'string', 'in:pending_payment,payment_expired,payment_failed,confirmed,staying,completed,cancelled,no_show,manual_review'],
            'keyword' => ['nullable', 'string', 'max:255'],
        ]);

        $bookings = Booking::with(['hotel', 'payment'])
            ->where('customer_id', auth()->id())
            ->when($data['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($data['keyword'] ?? null, function ($query, $keyword) {
                $query->where(function ($subQuery) use ($keyword) {
                    $subQuery->where('booking_code', 'like', "%{$keyword}%")
                        ->orWhereHas('hotel', function ($hotelQuery) use ($keyword) {
                            $hotelQuery->where('name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customer.bookings.history', [
            'bookings' => $bookings,
            'status' => $data['status'] ?? null,
            'keyword' => $data['keyword'] ?? null,
        ]);
    }

    public function show(Booking $booking)
    {
        $this->authorizeCustomer($booking);

        $booking->load([
            'hotel.owner',
            'roomTypes.roomType',
            'payment',
            'review',
        ]);

        return view('customer.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking, BookingMailService $bookingMailService)
    {
        $this->authorizeCustomer($booking);

        $data = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!in_array($booking->status, ['pending_payment', 'confirmed'], true)) {
            return back()->with('error', 'Đơn này không thể hủy ở trạng thái hiện tại. Nếu cần hỗ trợ, vui lòng liên hệ Admin Travel Mate.');
        }

        if ($booking->checkin_date->isToday() || $booking->checkin_date->isPast()) {
            return back()->with('error', 'Đơn đã đến ngày nhận phòng nên không thể hủy trực tuyến. Vui lòng liên hệ Admin Travel Mate để được kiểm tra chính sách xử lý.');
        }

        $resultMessage = 'Đơn đặt phòng đã được hủy.';

        DB::transaction(function () use ($booking, $data, &$resultMessage) {
            $booking->load(['payment', 'financialTransaction', 'hotel.owner']);

            $cancelReason = $data['cancel_reason'] ?: 'Khách hàng hủy đơn.';
            $isPaid = $booking->payment && $booking->payment->status === 'paid';

            $booking->update([
                'status' => 'cancelled',
                'cancel_reason' => $cancelReason,
                'cancelled_at' => now(),
            ]);

            if ($booking->payment) {
                if ($isPaid) {
                    $booking->payment->update([
                        'status' => 'refunding',
                        'refund_amount' => $booking->payment->amount,
                        'refund_reason' => $cancelReason,
                        'refund_note' => 'Khách đã gửi yêu cầu hủy trước ngày nhận phòng. Admin cần kiểm tra chính sách hủy/hoàn tiền của khách sạn trước khi xử lý.',
                    ]);

                    $resultMessage = 'Yêu cầu hủy đơn đã được ghi nhận. Vì đơn đã thanh toán, Admin sẽ kiểm tra chính sách của khách sạn trước khi xử lý hoàn tiền.';
                } elseif ($booking->payment->status === 'pending') {
                    $booking->payment->update([
                        'status' => 'failed',
                        'refund_amount' => 0,
                        'refund_reason' => $cancelReason,
                        'refund_note' => 'Đơn chưa thanh toán nên đã hủy ngay và không phát sinh hoàn tiền.',
                    ]);

                    $resultMessage = 'Đơn chưa thanh toán đã được hủy thành công. Không phát sinh hoàn tiền.';
                }
            }

            if (
                $booking->financialTransaction
                && $booking->financialTransaction->status !== 'settled'
            ) {
                $booking->financialTransaction->update([
                    'status' => 'adjusted',
                    'note' => $isPaid
                        ? 'Đơn đã bị hủy sau thanh toán. Admin cần kiểm tra hoàn tiền theo chính sách khách sạn.'
                        : 'Đơn chưa thanh toán đã bị hủy. Không phát sinh quyết toán.',
                ]);
            }
        });

        $freshBooking = $booking->fresh(['customer', 'hotel.owner', 'roomTypes.roomType', 'payment']);

        $bookingMailService->sendBookingCancellation($freshBooking);
        $bookingMailService->sendBookingCancellationToOwner($freshBooking);

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->with('success', $resultMessage);
    }

    private function authorizeCustomer(Booking $booking): void
    {
        if ((int) $booking->customer_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền xem hoặc thao tác trên đơn đặt phòng này.');
        }
    }
}
