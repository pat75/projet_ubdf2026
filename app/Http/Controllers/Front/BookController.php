<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactBookRequest;
use App\Models\User;
use App\Services\Auth\Recaptcha;
use App\Services\Book\ContexteBook;
use App\Services\Book\Gabarit;
use App\Services\Messagerie\DepotDemande;
use App\Support\Marque;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Book public, servi sur <login>.<book_domain>, dans son theme d'origine.
 *
 * Les onze habillages du legacy sont portes tels quels (lot 4d, voir
 * _doc/11_phase4_books.md). Ce controleur tient le role de
 * 2011_front/action_book.php : il prepare le contexte (ContexteBook) puis
 * rend le point d'entree du theme.
 *
 * Les URL sont celles du legacy, deja indexees :
 *
 *   /  /accueil                      accueil
 *   /portfolio                       portfolio, premiere galerie
 *   /<titre>-p<id>                   une galerie
 *   /news  /actualites               pages, premiere rubrique
 *   /<titre>-r<id>-c<id>             une page d'une rubrique
 *   /contact                         formulaire de contact
 *
 * Les identifiants sont ceux du legacy quand le contenu en vient
 * (`legacy_id`), les notres sinon.
 */
class BookController extends Controller
{
    public function accueil(string $login): Response
    {
        return $this->rendre($login, 'accueil');
    }

    public function portfolio(string $login): Response
    {
        return $this->rendre($login, 'portfolio');
    }

    public function galerie(string $login, string $titre, int $rub): Response
    {
        return $this->rendre($login, 'portfolio', $rub);
    }

    public function actualites(string $login): Response
    {
        return $this->rendre($login, 'news');
    }

    public function page(string $login, string $titre, int $rub, int $pag): Response
    {
        return $this->rendre($login, 'news', $rub, $pag);
    }

    public function contact(string $login): Response
    {
        return $this->rendre($login, 'contact');
    }

    /**
     * Envoi du formulaire de contact du book.
     *
     * La demande suit le meme chemin que celle deposee depuis la fiche du
     * portail (DepotDemande) : fil de discussion intermedie, detection du
     * spam, notification des deux parties. Le rappel JavaScript d'origine
     * attend `{error: bool}`.
     */
    public function envoyer(ContactBookRequest $requete, string $login, DepotDemande $depot, Recaptcha $recaptcha): JsonResponse
    {
        $book = User::where('login', $login)->firstOrFail();

        if (! $recaptcha->valideV2($requete->input('g-recaptcha-response'))) {
            return response()->json(['errors' => [__('Cochez la case « Je ne suis pas un robot ».')]]);
        }

        if ($depot->limiteAtteinte($requete->ip())) {
            return response()->json(['errors' => [__('Trop de demandes envoyées. Réessayez dans une heure.')]]);
        }

        $depot->deposer($book, [
            'action' => 'work_A_contact',
            'us_dir' => $book->login,
            'us_nom_prenom' => $requete->input('fm_contact_nom_prenom') ?: $requete->input('fm_contact_mail'),
            'us_mail' => $requete->input('fm_contact_mail'),
            'us_message' => $requete->input('fm_contact_message'),
        ], $requete->ip());

        return response()->json(['error' => false]);
    }

    private function rendre(string $login, string $type, int $rub = 0, int $pag = 0): Response
    {
        $book = User::with(['bookSetting', 'category'])->where('login', $login)->firstOrFail();

        $contexte = new ContexteBook($book, Marque::depuisCode($book->brand));
        $contexte->page_type = $type;
        $contexte->chargerPortfolio()->chargerPages();

        $theme = config('book_themes.'.$contexte->modele_book);

        /*
         | Book non diffuse sur le web : le legacy servait le gabarit
         | `non_diffuse` a tout visiteur, sauf a son proprietaire connecte
         | (action_book.php, l. 1210). Le proprietaire est ici l'utilisateur
         | authentifie, et non plus un cookie que chacun pouvait poser.
         */
        if (! $book->bookSetting?->diffuse_web && auth()->id() !== $book->id) {
            $contexte->url_mdl = $contexte->tpl_dir = 'non_diffuse';
            $contexte->page_type = 'non_diffuse';
            $contexte->chargerAccueil();

            return response(Gabarit::rendre('non_diffuse/ultrabook_2014_type', $contexte));
        }

        /*
         | « patch mdl 2014 » (action_book.php, l. 440) : pour ces quatre
         | themes, /portfolio sans galerie designee rend l'accueil.
         */
        if ($type === 'portfolio' && $rub === 0 && ! empty($theme['portfolio_vers_accueil'])) {
            $type = $contexte->page_type = 'accueil';
        }

        if ($theme['accueil'] === 'classique') {
            match ($type) {
                'accueil' => $contexte->classiqueAccueil(),
                'portfolio' => $contexte->classiquePortfolio($rub),
                'news' => $contexte->classiqueNews($rub, $pag),
                'contact' => $this->preparerContact($contexte, $theme),
            };

            return response(Gabarit::rendre($theme['dossier'].'/'.$theme['gabarit'], $contexte));
        }

        match ($type) {
            'accueil' => match ($theme['accueil']) {
                'portfolio' => $contexte->pagePortfolio(0),
                // Pinter : les pages d'accueil puis tout le portfolio, en
                // mosaique, sans nom de rubrique dans le titre.
                'mosaique' => $this->accueilDePages($contexte)->pagePortfolio(0, titre: false),
                default => $this->accueilDePages($contexte),
            },
            // Meme regle que pour les pages : rubrique 0, le gabarit choisit.
            'portfolio' => $contexte->pagePortfolio($rub),
            // Sans rubrique designee, le legacy passe 0 : c'est au gabarit de
            // choisir la page (front_nav_2011 retient la premiere page de la
            // derniere rubrique). Ne pas designer la premiere ici.
            'news' => $contexte->pageNews($rub, $pag),
            'contact' => $this->preparerContact($contexte, $theme),
        };

        return response(Gabarit::rendre($theme['dossier'].'/'.$theme['gabarit'], $contexte));
    }

    /**
     * Formulaire de contact : dans le gabarit contact du theme s'il en a un
     * (Zoom, 2020, classique 2010), sinon en page de rubrique.
     */
    private function preparerContact(ContexteBook $contexte, array $theme): void
    {
        $formulaire = view('book.contact', ['b' => $contexte])->render();
        $contexte->contact = $formulaire;

        if (($theme['contact'] ?? 'gabarit') === 'page') {
            $contexte->pageContact($formulaire);
        }
    }

    /**
     * mod_ptf_2012_accueil : les pages d'accueil (categorie 1). Le suffixe
     * du titre pour la formule gratuite est celui du legacy.
     */
    private function accueilDePages(ContexteBook $contexte): ContexteBook
    {
        $contexte->chargerAccueil();

        if ($contexte->us_formule < 1) {
            $contexte->cont_page_titre .= ' : '.$contexte->inc_site_name;
        }

        return $contexte;
    }

    /** Identifiant de la premiere rubrique d'une liste au format legacy. */
    private function premiereRubrique(array $rubriques): int
    {
        foreach ($rubriques as $cle => $rubrique) {
            if (is_int($cle)) {
                return (int) $rubrique['rub_id'];
            }
        }

        return 0;
    }
}
