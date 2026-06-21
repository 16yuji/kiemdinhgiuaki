<?php

namespace App\Services\Mail;

use App\Models\Booking;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ReviewMailService
{
    public function sendReviewInvitation(Booking $booking): bool
    {
        $booking->loadMissing([
            'customer',
            'hotel',
            'roomTypes.roomType',
            'review',
        ]);

        $recipient = $booking->contact_email ?: optional($booking->customer)->email;

        if (!$recipient) {
            SystemLogService::write(
                'review_invitation_mail_skipped',
                'mail',
                Booking::class,
                $booking->id,
                'Không có email người nhận để gửi lời mời đánh giá.',
                ['booking_code' => $booking->booking_code]
            );

            return false;
        }

        try {
            Mail::send('emails.reviews.invitation', ['booking' => $booking], function ($message) use ($recipient, $booking) {
                $message->to($recipient)->subject('Cảm ơn bạn đã lưu trú - Mời đánh giá đơn ' . $booking->booking_code);
            });

            SystemLogService::write(
                'review_invitation_mail_sent',
                'mail',
                Booking::class,
                $booking->id,
                'Gửi email cảm ơn và mời đánh giá sau check-out thành công.',
                [
                    'booking_code' => $booking->booking_code,
                    'recipient' => $recipient,
                ]
            );

            return true;
        } catch (Throwable $exception) {
            Log::warning('review_invitation_mail_failed', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            SystemLogService::write(
                'review_invitation_mail_failed',
                'mail',
                Booking::class,
                $booking->id,
                'Gửi email mời đánh giá thất bại nhưng check-out vẫn được lưu.',
                [
                    'booking_code' => $booking->booking_code,
                    'recipient' => $recipient,
                    'error' => $exception->getMessage(),
                ]
            );

            return false;
        }
    }
}
