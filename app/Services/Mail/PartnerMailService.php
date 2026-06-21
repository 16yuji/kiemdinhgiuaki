<?php

namespace App\Services\Mail;

use App\Models\PartnerRequest;
use App\Services\SystemLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PartnerMailService
{
    public function sendApproved(PartnerRequest $partnerRequest): bool
    {
        $partnerRequest->loadMissing(['user', 'reviewer']);
        $recipient = $this->recipient($partnerRequest);

        if (!$recipient) {
            return $this->logSkipped($partnerRequest, 'partner_approved_mail_skipped', 'Không có email người nhận để gửi thông báo duyệt đối tác.');
        }

        return $this->send(
            $recipient,
            'Yêu cầu đối tác đã được phê duyệt',
            'emails.partners.approved',
            ['partnerRequest' => $partnerRequest],
            $partnerRequest,
            'partner_approved_mail_sent',
            'Gửi email duyệt đối tác thành công.',
            'partner_approved_mail_failed'
        );
    }

    public function sendRejected(PartnerRequest $partnerRequest): bool
    {
        $partnerRequest->loadMissing(['user', 'reviewer']);
        $recipient = $this->recipient($partnerRequest);

        if (!$recipient) {
            return $this->logSkipped($partnerRequest, 'partner_rejected_mail_skipped', 'Không có email người nhận để gửi thông báo từ chối đối tác.');
        }

        return $this->send(
            $recipient,
            'Yêu cầu đối tác chưa được phê duyệt',
            'emails.partners.rejected',
            ['partnerRequest' => $partnerRequest],
            $partnerRequest,
            'partner_rejected_mail_sent',
            'Gửi email từ chối đối tác thành công.',
            'partner_rejected_mail_failed'
        );
    }

    private function recipient(PartnerRequest $partnerRequest): ?string
    {
        return $partnerRequest->contact_email ?: optional($partnerRequest->user)->email;
    }

    private function send(
        string $recipient,
        string $subject,
        string $view,
        array $data,
        PartnerRequest $partnerRequest,
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
                PartnerRequest::class,
                $partnerRequest->id,
                $successDescription,
                [
                    'recipient' => $recipient,
                    'business_name' => $partnerRequest->business_name,
                    'subject' => $subject,
                ]
            );

            return true;
        } catch (Throwable $exception) {
            Log::warning($failedAction, [
                'partner_request_id' => $partnerRequest->id,
                'recipient' => $recipient,
                'error' => $exception->getMessage(),
            ]);

            SystemLogService::write(
                $failedAction,
                'mail',
                PartnerRequest::class,
                $partnerRequest->id,
                'Gửi email thất bại nhưng nghiệp vụ chính vẫn được lưu.',
                [
                    'recipient' => $recipient,
                    'business_name' => $partnerRequest->business_name,
                    'subject' => $subject,
                    'error' => $exception->getMessage(),
                ]
            );

            return false;
        }
    }

    private function logSkipped(PartnerRequest $partnerRequest, string $action, string $description): bool
    {
        SystemLogService::write(
            $action,
            'mail',
            PartnerRequest::class,
            $partnerRequest->id,
            $description,
            ['business_name' => $partnerRequest->business_name]
        );

        return false;
    }
}
