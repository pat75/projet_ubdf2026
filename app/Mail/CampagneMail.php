<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Services\Newsletter\DestinataireNewsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/** Un envoi de campagne (newsletter ou relance redigee au back-office). */
class CampagneMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Campaign $campagne,
        public readonly DestinataireNewsletter $destinataire,
        public readonly string $lienDesabonnement,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campagne->subject);
    }

    /**
     * Desabonnement en un geste depuis la boite de reception : Gmail et
     * Yahoo l'exigent des expediteurs en masse. `One-Click` demande un
     * POST, que nos routes signees n'acceptent pas : on s'en tient au lien.
     */
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->lienDesabonnement.'>',
        ]);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.campagne', with: [
            'corps' => $this->corps(),
        ]);
    }

    /** Le corps redige au back-office, ou `{prenom}` est remplace. */
    public function corps(): string
    {
        return str_replace('{prenom}', e($this->destinataire->prenom()), (string) $this->campagne->body);
    }
}
