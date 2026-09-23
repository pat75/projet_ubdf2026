<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Un envoi de campagne (newsletter ou relance redigee au back-office). */
class CampagneMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Campaign $campagne,
        public readonly User $destinataire,
        public readonly string $lienDesabonnement,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campagne->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.campagne');
    }
}
