<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $otp,
        public string $expiresAt,
        public string $requestIp,
    ) {}

    public function envelope(): Envelope
    {
        $adminEmail = config('mail.admin_otp_email');

        return new Envelope(
            to: array_filter([$adminEmail]),
            subject: 'Password Reset OTP — ' . $this->user->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
