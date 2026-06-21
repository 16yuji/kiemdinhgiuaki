<?php

namespace App\Services\Mail;

use App\Models\Booking;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingMailService
{
    public function sendBookingConfirmation(Booking $booking): bool
    {
        $booking->loadMissing([
            'customer',
            'hotel.owner',
            'roomTypes.roomType',
            'payment',
        ]);

        $recipient = $this->bookingRecipient($booking);

        if (!$recipient) {
            return $this->logSkipped($booking, 'booking_confirmation_mail_skipped', 'Không có email người nhận để gửi xác nhận đặt phòng.');
        }

        return $this->send(
            $recipient,
            'Xác nhận đặt phòng ' . $booking->booking_code,
            'emails.bookings.confirmation',
            ['booking' => $booking],
            $booking,
            'booking_confirmation_mail_sent',
            'Gửi email xác nhận đặt phòng thành công.',
            'booking_confirmation_mail_failed'
        );
    }

    public function sendBookingCancellation(Booking $booking): bool
    {
        $booking->loadMissing([
            'customer',
            'hotel.owner',
            'roomTypes.roomType',
            'payment',
        ]);

        $recipient = $this->bookingRecipient($booking);

        if (!$recipient) {
            return $this->logSkipped($booking, 'booking_cancellation_mail_skipped', 'Không có email người nhận để gửi xác nhận hủy đơn.');
        }

        return $this->send(
            $recipient,
            'Xác nhận hủy đơn ' . $booking->booking_code,
            'emails.bookings.cancellation',
            ['booking' => $booking],
            $booking,
            'booking_cancellation_mail_sent',
            'Gửi email xác nhận hủy đơn thành công.',
            'booking_cancellation_mail_failed'
        );
    }


    public function sendBookingCancellationToOwner(Booking $booking): bool
    {
        $booking->loadMissing([
            'customer',
            'hotel.owner',
            'roomTypes.roomType',
            'payment',
        ]);

        $recipient = optional(optional($booking->hotel)->owner)->email;

        if (!$recipient) {
            return $this->logSkipped($booking, 'owner_booking_cancellation_mail_skipped', 'Không có email Owner để gửi thông báo hủy đơn.');
        }

        return $this->send(
            $recipient,
            'Khách đã hủy đơn ' . $booking->booking_code,
            'emails.bookings.owner-cancellation',
            ['booking' => $booking],
            $booking,
            'owner_booking_cancellation_mail_sent',
            'Gửi email thông báo hủy đơn cho Owner thành công.',
            'owner_booking_cancellation_mail_failed'
        );
    }

    private function bookingRecipient(Booking $booking): ?string
    {
        return $booking->contact_email ?: optional($booking->customer)->email;
    }

    private function send(
        string $recipient,
        string $subject,
        string $view,
        array $data,
        Booking $booking,
        string $successAction,
        string $successDescription,
        string $failedAction
    ): bool {
        try {
            Mail::send($view, $data, function ($message) use ($recipient, $subject) {
                $message->to($recipient)->subject($subject);
            });

            SystemLogService::write(
                $successAction,
                'mail',
                Booking::class,
                $booking->id,
                $successDescription,
                [
                    'booking_code' => $booking->booking_code,
                    'recipient' => $recipient,
                    'subject' => $subject,
                ]
            );

            return true;
        } catch (Throwable $exception) {
            Log::warning($failedAction, [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            SystemLogService::write(
                $failedAction,
                'mail',
                Booking::class,
                $booking->id,
                'Gửi email thất bại nhưng nghiệp vụ chính vẫn được lưu.',
                [
                    'booking_code' => $booking->booking_code,
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'error' => $exception->getMessage(),
                ]
            );

            return false;
        }
    }

    private function logSkipped(Booking $booking, string $action, string $description): bool
    {
        SystemLogService::write(
            $action,
            'mail',
            Booking::class,
            $booking->id,
            $description,
            ['booking_code' => $booking->booking_code]
        );

        return false;
    }
}
