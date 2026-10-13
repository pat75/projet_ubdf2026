<?php

use App\Mail\ConfirmationAdresse;
use App\Mail\SupportReponse;
use App\Models\SupportTicket;
use App\Models\User;
use App\Support\Marque;

/*
 | Le gabarit commun des mails (vendor/mail/html/message) affiche le logo,
 | le lien et le pied de la marque du mail, pas toujours ceux d'Ultra-book.
 */

it('affiche le logo de la marque de la demande dans la reponse du support', function (string $brand, string $logo, string $nom) {
    $ticket = SupportTicket::create(['email' => 'claire@example.com', 'brand' => $brand]);
    $ticket->ajouter('admin', 'Bonjour');

    $mail = new SupportReponse($ticket);
    $html = $mail->render();

    expect($html)->toContain($logo)->toContain('© '.date('Y').' '.$nom.'. Tous droits réservés.')
        ->toContain('href="'.Marque::depuisCode($brand)->canonique.'"');
    $mail->assertHasSubject('Réponse du support '.$nom);
})->with([
    'Ultra-book' => ['ub', 'img_admin/ultra-book_logo_nb.gif', 'Ultra-book'],
    'Dustfolio' => ['df', 'img_front_df/mail_logo_top.gif', 'Dustfolio'],
]);

it('suit la marque du compte destinataire', function () {
    $compte = User::factory()->create(['login' => 'dusty', 'brand' => 'df']);

    $html = (new ConfirmationAdresse($compte, 'https://exemple.test/lien'))->render();

    expect($html)->toContain('img_front_df/mail_logo_top.gif')->not->toContain('ultra-book_logo_nb.gif');
});

it('ne garde pas la marque d un mail precedent', function () {
    $df = User::factory()->create(['login' => 'dusty', 'brand' => 'df']);
    $ub = User::factory()->create(['login' => 'ultra', 'brand' => 'ub']);

    (new ConfirmationAdresse($df, 'https://exemple.test/a'))->render();
    $html = (new ConfirmationAdresse($ub, 'https://exemple.test/b'))->render();

    expect($html)->toContain('ultra-book_logo_nb.gif');
});
