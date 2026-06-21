<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Illuminate\Http\Request;

class VnpayService
{
    public function createPaymentUrl(Booking $booking, Request $request): string
    {
        $vnpUrl = config('services.vnpay.payment_url');
        $vnpTmnCode = config('services.vnpay.tmn_code');
        $vnpHashSecret = config('services.vnpay.hash_secret');
        $vnpReturnUrl = config('services.vnpay.return_url');

        if (!$vnpUrl || !$vnpTmnCode || !$vnpHashSecret || !$vnpReturnUrl) {
            throw new \RuntimeException('Thiếu cấu hình VNPAY trong .env hoặc config/services.php.');
        }

        /*
         * Dùng booking id trong vnp_TxnRef để callback tìm đơn chắc chắn hơn.
         * Format: bookingId_timestamp
         */
        $txnRef = $booking->id . '_' . time();

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $vnpTmnCode,
            'vnp_Amount' => (int) round($booking->total_amount * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toan don dat phong ' . $booking->booking_code,
            'vnp_OrderType' => 'billpayment',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $vnpReturnUrl,
            'vnp_IpAddr' => $request->ip(),
            'vnp_CreateDate' => now()->format('YmdHis'),
        ];

        ksort($inputData);

        $hashData = '';
        $query = '';

        foreach ($inputData as $key => $value) {
            $encodedKey = urlencode($key);
            $encodedValue = urlencode($value);

            $hashData .= ($hashData ? '&' : '') . $encodedKey . '=' . $encodedValue;
            $query .= $encodedKey . '=' . $encodedValue . '&';
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        return $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $secureHash;
    }

    public function verifySignature(Request $request): bool
    {
        $vnpHashSecret = config('services.vnpay.hash_secret');

        if (!$vnpHashSecret) {
            return false;
        }

        $inputData = [];

        foreach ($request->query() as $key => $value) {
            if (str_starts_with($key, 'vnp_')) {
                $inputData[$key] = $value;
            }
        }

        $secureHash = $inputData['vnp_SecureHash'] ?? null;

        if (!$secureHash) {
            return false;
        }

        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashData = '';

        foreach ($inputData as $key => $value) {
            $hashData .= ($hashData ? '&' : '') . urlencode($key) . '=' . urlencode($value);
        }

        $checkHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        return hash_equals($checkHash, $secureHash);
    }

    public function extractBookingId(?string $txnRef): ?int
    {
        if (!$txnRef) {
            return null;
        }

        $id = explode('_', $txnRef)[0] ?? null;

        return is_numeric($id) ? (int) $id : null;
    }

    public function isSuccessfulPayment(Request $request): bool
    {
        return $request->query('vnp_ResponseCode') === '00'
            && $request->query('vnp_TransactionStatus') === '00';
    }
}