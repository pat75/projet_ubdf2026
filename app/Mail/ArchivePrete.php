<?php

namespace App\Mail;

use App\Support\Marque;
use App\Models\DataExport;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Avis au createur : son archive « Mes donnees » est prete au telechargement. */
class ArchivePrete extends Mailable
{
    public function __construct(
        public readonly DataExport $export,
        public readonly string $lien,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('Votre archive est prête'));
    }

    public function content(): Content
    {
        // Logo et pied du gabarit commun (Marque::duMail()).
        Marque::pourMail($this->export->user?->brand);

        return new Content(markdown: 'mail.archive-prete');
    }
}
