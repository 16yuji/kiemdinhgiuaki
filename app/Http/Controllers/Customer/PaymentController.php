<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FinancialTransaction;
use App\Services\Mail\BookingMailService;
use App\Services\Payment\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PaymentController extends Controller
{
    public function checkout(Booking $booking)
    {
        $this->authorizeCustomerBooking($booking);

        $booking->load([
            'hotel',
            'roomTypes.roomType',
            'payment',
        ]);

        if ($booking->status !== 'pending_payment') {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Đơn này không còn ở trạng thái chờ thanh toán.');
        }

        if ($booking->hold_expires_at && $booking->hold_expires_at->isPast()) {
            DB::transaction(function () use ($booking) {
                $booking->update([
                    'status' => 'payment_expired',
                ]);

                if ($booking->payment && $booking->payment->status === 'pending') {
                    $booking->payment->update([
                        'status' => 'expired',
                    ]);
                }
            });

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Đơn đặt phòng đã hết hạn thanh toán.');
        }

        return view('customer.payments.checkout', compact('booking'));
    }

    public function simulateSuccess(Booking $booking)
    {
        $this->authorizeCustomerBooking($booking);

        $booking->load(['payment', 'hotel', 'customer', 'roomTypes.roomType']);

        if ($booking->status !== 'pending_payment') {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Đơn này không còn ở trạng thái chờ thanh toán.');
        }

        if ($booking->hold_expires_at && $booking->hold_expires_at->isPast()) {
            DB::transaction(function () use ($booking) {
                $booking->update([
                    'status' => 'payment_expired',
                ]);

                if ($booking->payment && $booking->payment->status === 'pending') {
                    $booking->payment->update([
                        'status' => 'expired',
                    ]);
                }
            });

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Đơn đặt phòng đã hết hạn thanh toán.');
        }

        $paymentResult = $this->markBookingPaid(
            booking: $booking,
            method: 'fake',
            transactionCode: 'FAKE-' . now()->format('YmdHis'),
            note: 'Ghi nhận doanh thu tạm thời sau thanh toán giả lập.'
        );

        if ($paymentResult === 'paid') {
            $this->sendBookingConfirmationMail($booking);
        }

        if ($paymentResult === 'manual_review') {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('warning', 'Thanh toán đã được ghi nhận nhưng đơn cần kiểm tra thủ công vì thời gian giữ phòng đã hết hoặc trạng thái phòng đã thay đổi.');
        }

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->with('success', 'Thanh toán giả lập thành công. Đơn đặt phòng đã được xác nhận.');
    }

    public function vnpay(Request $request, Booking $booking, VnpayService $vnpayService)
    {
        $this->authorizeCustomerBooking($booking);

        $booking->load(['payment']);

        if ($booking->status !== 'pending_payment') {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Đơn này không còn ở trạng thái chờ thanh toán.');
        }

        if ($booking->hold_expires_at && $booking->hold_expires_at->isPast()) {
            DB::transaction(function () use ($booking) {
                $booking->update([
                    'status' => 'payment_expired',
                ]);

                if ($booking->payment && $booking->payment->status === 'pending') {
                    $booking->payment->update([
                        'status' => 'expired',
                    ]);
                }
            });

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Đơn đặt phòng đã hết hạn thanh toán.');
        }

        if (!$booking->payment) {
            $booking->payment()->create([
                'amount' => $booking->total_amount,
                'method' => 'vnpay',
                'status' => 'pending',
            ]);
        } else {
            $booking->payment->update([
                'amount' => $booking->total_amount,
                'method' => 'vnpay',
                'status' => 'pending',
            ]);
        }

        return redirect()->away($vnpayService->createPaymentUrl($booking, $request));
    }

    public function vnpayReturn(Request $request, VnpayService $vnpayService)
    {
        if (!$vnpayService->verifySignature($request)) {
            return redirect()
                ->route('customer.bookings.history')
                ->with('error', 'Chữ ký thanh toán VNPAY không hợp lệ.');
        }

        $bookingId = $vnpayService->extractBookingId($request->query('vnp_TxnRef'));
        $booking = $bookingId ? Booking::find($bookingId) : null;

        if (!$booking) {
            return redirect()
                ->route('customer.bookings.history')
                ->with('error', 'Không tìm thấy đơn đặt phòng tương ứng.');
        }

        $vnpAmount = (int) $request->query('vnp_Amount');
        $systemAmount = (int) round($booking->total_amount * 100);

        if ($vnpAmount !== $systemAmount) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('error', 'Số tiền thanh toán VNPAY không khớp với đơn đặt phòng.');
        }

        if ($vnpayService->isSuccessfulPayment($request)) {
            $transactionCode = $request->query('vnp_TransactionNo')
                ?: $request->query('vnp_BankTranNo')
                ?: $request->query('vnp_TxnRef');

            try {
                $paymentResult = $this->markBookingPaid(
                    booking: $booking,
                    method: 'vnpay',
                    transactionCode: $transactionCode,
                    note: 'Ghi nhận doanh thu tạm thời sau thanh toán VNPAY.',
                    gatewayRequest: $request
                );
            } catch (\RuntimeException $exception) {
                return redirect()
                    ->route('customer.bookings.show', $booking)
                    ->with('error', $exception->getMessage());
            }

            if ($paymentResult === 'paid') {
                $this->sendBookingConfirmationMail($booking);
            }

            if ($paymentResult === 'manual_review') {
                return redirect()
                    ->route('customer.bookings.show', $booking)
                    ->with('warning', 'Thanh toán VNPAY đã được ghi nhận nhưng đơn đã hết hạn giữ phòng hoặc cần kiểm tra lại tình trạng phòng. Travel Mate sẽ xử lý thủ công trước khi xác nhận lưu trú.');
            }

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('success', 'Thanh toán VNPAY thành công. Đơn đặt phòng đã được xác nhận.');
        }

        $this->markBookingPaymentFailed($booking, $request);

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->with('error', 'Thanh toán VNPAY không thành công hoặc đã bị hủy.');
    }

    public function vnpayIpn(Request $request, VnpayService $vnpayService)
    {
        if (!$vnpayService->verifySignature($request)) {
            return response()->json([
                'RspCode' => '97',
                'Message' => 'Invalid signature',
            ]);
        }

        $bookingId = $vnpayService->extractBookingId($request->query('vnp_TxnRef'));
        $booking = $bookingId ? Booking::find($bookingId) : null;

        if (!$booking) {
            return response()->json([
                'RspCode' => '01',
                'Message' => 'Order not found',
            ]);
        }

        $vnpAmount = (int) $request->query('vnp_Amount');
        $systemAmount = (int) round($booking->total_amount * 100);

        if ($vnpAmount !== $systemAmount) {
            return response()->json([
                'RspCode' => '04',
                'Message' => 'Invalid amount',
            ]);
        }

        if ($booking->payment && $booking->payment->status === 'paid') {
            return response()->json([
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);
        }

        if ($vnpayService->isSuccessfulPayment($request)) {
            $transactionCode = $request->query('vnp_TransactionNo')
                ?: $request->query('vnp_BankTranNo')
                ?: $request->query('vnp_TxnRef');

            try {
                $paymentResult = $this->markBookingPaid(
                    booking: $booking,
                    method: 'vnpay',
                    transactionCode: $transactionCode,
                    note: 'Ghi nhận doanh thu tạm thời sau IPN VNPAY.',
                    gatewayRequest: $request
                );
            } catch (\RuntimeException $exception) {
                return response()->json([
                    'RspCode' => '02',
                    'Message' => $exception->getMessage(),
                ]);
            }

            if ($paymentResult === 'paid') {
                $this->sendBookingConfirmationMail($booking);
            }

            return response()->json([
                'RspCode' => '00',
                'Message' => 'Confirm Success',
            ]);
        }

        $this->markBookingPaymentFailed($booking, $request);

        return response()->json([
            'RspCode' => '00',
            'Message' => 'Confirm Success',
        ]);
    }

    private function markBookingPaid(
        Booking $booking,
        string $method,
        string $transactionCode,
        string $note,
        ?Request $gatewayRequest = null
    ): string {
        return DB::transaction(function () use ($booking, $method, $transactionCode, $note, $gatewayRequest) {
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $lockedBooking->load('hotel');
            $payment = $lockedBooking->payment()->lockForUpdate()->first();

            if ($payment && $payment->status === 'paid') {
                return 'already_paid';
            }

            $paymentData = array_merge([
                'status' => 'paid',
                'method' => $method,
                'amount' => $lockedBooking->total_amount,
                'transaction_code' => $transactionCode,
                'paid_at' => now(),
            ], $this->gatewayPaymentAttributes($gatewayRequest));

            $holdExpired = $lockedBooking->hold_expires_at && $lockedBooking->hold_expires_at->isPast();
            $requiresManualReview = $holdExpired
                || (
                    $method === 'vnpay'
                    && in_array($lockedBooking->status, ['payment_expired', 'payment_failed', 'cancelled', 'manual_review'], true)
                );

            if ($requiresManualReview) {
                $lockedBooking->update([
                    'status' => 'manual_review',
                    'manual_review_reason' => 'Thanh toán được ghi nhận sau khi đơn không còn trong thời gian giữ phòng hoặc cần kiểm tra lại trạng thái phòng. Owner/Admin cần kiểm tra phòng trống hoặc phương án xử lý với khách.',
                ]);
            } elseif ($lockedBooking->status === 'pending_payment') {
                $lockedBooking->update([
                    'status' => 'confirmed',
                ]);
            } elseif ($lockedBooking->status !== 'confirmed') {
                throw new \RuntimeException('Đơn không còn đủ điều kiện xác nhận thanh toán tự động. Vui lòng liên hệ Travel Mate để kiểm tra.');
            }

            if ($payment) {
                $payment->update($paymentData);
            } else {
                $lockedBooking->payment()->create($paymentData);
            }

            $platformFee = round($lockedBooking->total_amount * 0.15, 2);
            $ownerAmount = round($lockedBooking->total_amount - $platformFee, 2);

            FinancialTransaction::updateOrCreate(
                [
                    'booking_id' => $lockedBooking->id,
                ],
                [
                    'owner_id' => $lockedBooking->hotel->owner_id,
                    'gross_amount' => $lockedBooking->total_amount,
                    'platform_fee' => $platformFee,
                    'owner_amount' => $ownerAmount,
                    'status' => 'temporary_recorded',
                    'note' => $note,
                ]
            );

            return $requiresManualReview ? 'manual_review' : 'paid';
        });
    }

    private function markBookingPaymentFailed(Booking $booking, ?Request $gatewayRequest = null): void
    {
        DB::transaction(function () use ($booking, $gatewayRequest) {
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $payment = $lockedBooking->payment()->lockForUpdate()->first();

            if ($payment && $payment->status === 'paid') {
                return;
            }

            if ($lockedBooking->status === 'pending_payment') {
                $lockedBooking->update([
                    'status' => 'payment_failed',
                ]);
            }

            if ($payment && $payment->status === 'pending') {
                $payment->update(array_merge([
                    'status' => 'failed',
                    'method' => 'vnpay',
                ], $this->gatewayPaymentAttributes($gatewayRequest)));
            }
        });
    }

    private function gatewayPaymentAttributes(?Request $request): array
    {
        if (!$request) {
            return [];
        }

        $attributes = [];

        if (Schema::hasColumn('payments', 'gateway_response_code')) {
            $attributes['gateway_response_code'] = $request->query('vnp_ResponseCode');
        }

        if (Schema::hasColumn('payments', 'gateway_payload')) {
            $attributes['gateway_payload'] = $request->query();
        }

        return $attributes;
    }

    private function sendBookingConfirmationMail(Booking $booking): void
    {
        try {
            $booking->load(['customer', 'hotel', 'roomTypes.roomType', 'payment']);

            if (class_exists(BookingMailService::class)) {
                app(BookingMailService::class)->sendBookingConfirmation($booking);
            }
        } catch (\Throwable $exception) {
            Log::warning('Không gửi được email xác nhận đặt phòng.', [
                'booking_id' => $booking->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function authorizeCustomerBooking(Booking $booking): void
    {
        abort_unless($booking->customer_id === auth()->id(), 403);
    }
}
