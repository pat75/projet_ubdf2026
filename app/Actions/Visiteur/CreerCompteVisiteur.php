<?php

namespace App\Actions\Visiteur;

use App\Mail\BienvenueVisiteur;
use App\Models\NewsletterMail;
use App\Models\Visitor;
use App\Services\Memo\MemoBooks;
use App\Support\Marque;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Ouvre un compte visiteur, y verse la selection du navigateur, l'inscrit
 * a la newsletter et envoie le mail de confirmation d'adresse.
 *
 * La confirmation ne bloque rien : le memo est utilisable tout de suite.
 * Elle ne conditionne que l'affichage des messages envoyes avec cette
 * adresse (MemoBooks::conversations()).
 */
class CreerCompteVisiteur
{
    public function __construct(private readonly MemoBooks $memo) {}

    /** @param  list<string>  $logins */
    public function executer(
        string $email, string $motDePasse, array $logins, Marque $marque, ?string $ip,
        ?string $prenom = null, ?string $nom = null,
    ): Visitor {
        $visiteur = Visitor::query()->create([
            'email' => $email,
            'firstname' => $prenom,
            'lastname' => $nom,
            'password' => $motDePasse,
            'brand' => $marque->code,
            'locale' => app()->getLocale(),
            'signup_ip' => $ip,
        ]);

        $this->memo->fusionner($visiteur, $logins);

        // Newsletter activee par defaut ; le visiteur la coupe dans « Mon compte ».
        NewsletterMail::query()->firstOrCreate(['email' => $visiteur->email], ['ip' => $ip]);

        Mail::to($visiteur->email)->send(new BienvenueVisiteur($visiteur, $marque, $this->lienConfirmation($visiteur)));

        return $visiteur;
    }

    public function lienConfirmation(Visitor $visiteur): string
    {
        return URL::temporarySignedRoute(
            nom_route('visiteur.confirmer'),
            now()->addDays(7),
            ['visitor' => $visiteur->id, 'hash' => sha1($visiteur->email)],
        );
    }
}
