<?php

namespace App\Services\Auth;

use App\Mail\BienvenueCreatif;
use App\Models\BookSetting;
use App\Models\Category;
use App\Models\User;
use App\Support\Marque;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Creation d'un compte creatif.
 *
 * Le legacy creait la ligne `inc_user` d'abord, puis la completait par un
 * `UPDATE` construit a partir du tableau `$_POST` (« user2010_user_update_table
 * ('inc_user', $form2->form_data_post, $id) ») : toute cle postee finissait
 * en colonne. Ici les champs sont nommes un a un.
 */
class Inscription
{
    /** Le login devient un sous-domaine : seuls ces caracteres sont admis. */
    public const MOTIF_LOGIN = '/^[a-z0-9_-]+$/';

    public function __construct(private readonly MotDePasse $motDePasse) {}

    /**
     * Le login est-il disponible ?
     *
     * Le legacy ne consultait que la table des comptes. Il ne verifiait ni
     * le motif ni les etiquettes de sous-domaine reservees : rien
     * n'empechait d'ouvrir un book « www » ou « df », qui aurait alors
     * masque le portail lui-meme.
     */
    public function loginDisponible(string $login): bool
    {
        $login = mb_strtolower(trim($login));

        if ($login === '' || preg_match(self::MOTIF_LOGIN, $login) !== 1) {
            return false;
        }

        if (Marque::sousDomaineReserve($login)) {
            return false;
        }

        return ! User::query()->where('login', $login)->exists();
    }

    /**
     * @param  array{login:string,email:string,password:string,nom:string,categorie:?string}  $champs
     */
    public function creer(array $champs, Marque $marque, ?string $ip = null, ?string $referer = null): User
    {
        [$nom, $prenom] = $this->nomPrenom($champs['nom'] ?? '');

        return DB::transaction(function () use ($champs, $marque, $ip, $referer, $nom, $prenom) {
            $compte = User::query()->create([
                'login' => mb_strtolower(trim($champs['login'])),
                'email' => $champs['email'],
                'password' => $champs['password'],
                'lastname' => $nom,
                'firstname' => $prenom,
                'category_id' => $this->categorieId($champs['categorie'] ?? null),
                'brand' => $marque->code,
                'locale' => app()->getLocale(),
                'signup_ip' => $ip,
                'signup_referer' => $referer,
            ]);

            // Reglages par defaut (Ultra-frais, diffuse partout) : voir BookSetting::$attributes.
            BookSetting::query()->create([
                'user_id' => $compte->id,
                'title' => $compte->fullName(),
            ]);

            // Un premier portfolio vide, pret a recevoir les visuels.
            $compte->galleries()->create([
                'name' => __('Portfolio :n', ['n' => 1]),
                'slug' => 'portfolio-1',
                'status' => 'published',
                'position' => 1,
            ]);

            return $compte;
        });
    }

    /**
     * Envoie le mail de bienvenue, qui porte le lien de confirmation.
     *
     * Le compte existe deja quand il part : un envoi qui echoue (SMTP
     * indisponible…) est journalise sans faire echouer l'inscription, qui
     * laisserait le createur sur une erreur, son identifiant deja pris.
     */
    public function envoyerBienvenue(User $compte, Marque $marque): void
    {
        rescue(fn () => Mail::to($compte->email)->send(new BienvenueCreatif(
            $compte,
            $marque,
            $this->lienConfirmation($compte),
        )));
    }

    public function lienConfirmation(User $compte): string
    {
        return URL::temporarySignedRoute(
            nom_route('inscription.confirmer'),
            now()->addDays(7),
            ['user' => $compte->login],   // getRouteKeyName() = login
        );
    }

    /**
     * « Nom / Prenom » : le premier mot est le nom, le reste le prenom.
     * C'est la lecture du legacy, et l'ordre affiche par le formulaire.
     * Contrairement a lui, un prenom en deux mots n'est plus tronque.
     */
    private function nomPrenom(string $saisie): array
    {
        $mots = preg_split('/\s+/', trim($saisie), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return [
            $mots[0] ?? null,
            count($mots) > 1 ? implode(' ', array_slice($mots, 1)) : null,
        ];
    }

    private function categorieId(?string $slug): ?int
    {
        if (blank($slug)) {
            return null;
        }

        return Category::query()->where('slug', $slug)->value('id');
    }
}
