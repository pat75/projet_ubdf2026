<?php

namespace App\Livewire\Espace;

use App\Models\Domaine;
use App\Services\Domaines\AchatDomaine;
use App\Services\Domaines\BrancherDomaine;
use App\Services\Paiement\PaiementDomaine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RuntimeException;

/**
 * Option « Réserver mon propre nom de domaine », sous les formules :
 * recherche -> titulaire et consentements -> paiement Payplug. La
 * commande OVH ne part qu'une fois le paiement relu chez Payplug.
 *
 * Reserve au developpement (Domaine::achatOuvert) : filet rouge dev_only.
 */
class NomDeDomaine extends Component
{
    public string $recherche = '';

    /** Extension choisie dans le selecteur du champ de recherche. */
    public string $extension = 'fr';

    /** @var array<string, array{nom: string, disponible: bool, prix: float}> */
    #[Locked]
    public array $resultats = [];

    /** Nom choisi parmi les resultats disponibles. */
    #[Locked]
    public ?string $choix = null;

    /** @var array<string, string> */
    public array $titulaire = [];

    public bool $cgu = false;

    /** Domaine deja possede ailleurs, saisi par le createur. */
    public string $existant = '';

    public bool $renonciation = false;

    public function mount(PaiementDomaine $paiement): void
    {
        abort_unless(Domaine::achatOuvert(), 404);

        // Retour de Payplug : le paiement est relu chez Payplug, pas cru sur parole.
        if (request()->query('domaine') === 'retour' && ($attente = $this->domaine('a_payer'))) {
            try {
                $paiement->confirmer($attente);

                // Message de remerciement du layout (lu apres le rendu de la page).
                if ($attente->fresh()->statut !== 'a_payer') {
                    session()->now('paiement_ok', true);
                    session()->now('paiement', __('Merci. Votre domaine vient d’être réservé.'));
                    session()->now('paiement_detail', __('Retrouvez toutes ses informations dans le menu Nom de domaine.'));
                    session()->now('paiement_lien', [route(nom_route('espace.domaine')), __('Gérer mon nom de domaine')]);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function rechercher(AchatDomaine $achat): void
    {
        $this->reset('resultats', 'choix');

        // « jeanne.com » tape en entier : le selecteur suit l'extension saisie.
        if (preg_match('/\.(fr|com)\s*$/i', $this->recherche, $m)) {
            $this->extension = strtolower($m[1]);
        }

        abort_unless(Domaine::prix($this->extension) !== null, 422);
        $libelle = $achat->libelle($this->recherche);

        if (! Domaine::libelleValide($libelle)) {
            $this->addError('recherche', __('Lettres, chiffres et tirets seulement (63 caractères au plus).'));

            return;
        }

        $cle = 'recherche-domaine|'.Auth::id();
        abort_if(RateLimiter::tooManyAttempts($cle, 30), 429);
        RateLimiter::hit($cle, 600);

        $this->recherche = $libelle;
        $this->resultats = $achat->rechercher($libelle, [$this->extension]);

        // Domaine libre : on passe directement au formulaire.
        if ($this->resultats[$this->extension]['disponible'] ?? false) {
            $this->choisir($this->extension, $achat);
        }
    }

    public function choisir(string $extension, AchatDomaine $achat): void
    {
        abort_unless($this->resultats[$extension]['disponible'] ?? false, 422);

        $this->choix = $this->resultats[$extension]['nom'];
        $this->titulaire = $achat->titulaireDepuis(Auth::user());

        // Recette locale : champs obligatoires souvent vides sur un compte de test.
        if (app()->isLocal()) {
            $this->titulaire = array_merge(
                ['telephone' => '+33.612345678', 'adresse' => '12 rue de Test', 'code_postal' => '75011', 'ville' => 'Paris'],
                array_filter($this->titulaire),
            );
            $this->cgu = $this->renonciation = true;
        }
    }

    public function changer(): void
    {
        $this->reset('choix', 'cgu', 'renonciation');
    }

    /** Verification OVH, enregistrement des consentements, puis page de paiement Payplug. */
    public function payer(AchatDomaine $achat, PaiementDomaine $paiement): void
    {
        abort_if($this->choix === null, 422);
        $extension = substr(strrchr($this->choix, '.'), 1);

        $this->titulaire['telephone'] = $achat->telephone((string) ($this->titulaire['telephone'] ?? ''), (string) ($this->titulaire['pays'] ?? 'FR'));
        $this->validate($achat->reglesTitulaire($extension) + [
            'cgu' => ['accepted'],
            'renonciation' => ['accepted'],
        ], [
            'titulaire.pays.in' => $extension === 'fr'
                ? __('Un .fr est réservé aux titulaires résidant dans l’Union européenne.')
                : __('Choisissez un pays dans la liste.'),
            'titulaire.telephone.regex' => __('Format international attendu, par exemple +33.612345678.'),
            'cgu.accepted' => __('Acceptez les conditions générales pour continuer.'),
            'renonciation.accepted' => __('Cette renonciation est nécessaire pour enregistrer le domaine.'),
        ]);

        try {
            $domaine = $achat->preparer(Auth::user(), $this->choix, $this->titulaire, request()->ip());
            $url = $paiement->commencer($domaine);
        } catch (RuntimeException $e) {
            $this->addError('choix', $e->getMessage());

            return;
        }

        // Fenetre flottante Payplug (script de espace/formule).
        $this->dispatch('payplug-ouvrir', url: $url);
    }

    /** « J'ai déjà un nom de domaine » : declaration, puis page de configuration. */
    public function brancher(BrancherDomaine $brancher): void
    {
        $this->validate(['existant' => 'required|string|max:253']);

        $cle = 'brancher-domaine|'.Auth::id();
        abort_if(RateLimiter::tooManyAttempts($cle, 10), 429);
        RateLimiter::hit($cle, 600);

        try {
            $brancher->declarer(Auth::user(), $this->existant);
        } catch (RuntimeException $e) {
            $this->addError('existant', $e->getMessage());

            return;
        }

        $this->redirectRoute(nom_route('espace.domaine'));
    }

    public function render(): View
    {
        return view('livewire.espace.nom-de-domaine', [
            'domaine' => Domaine::where('user_id', Auth::id())
                ->whereIn('statut', [...Domaine::EN_COURS, 'echec_commande', 'rembourse'])
                ->latest('id')->first(),
            'pays' => $this->choix && str_ends_with($this->choix, '.fr')
                ? array_intersect_key(config('domaines.pays'), array_flip(config('domaines.ue')))
                : config('domaines.pays'),
        ]);
    }

    private function domaine(string $statut): ?Domaine
    {
        return Domaine::where('user_id', Auth::id())->where('statut', $statut)->latest('id')->first();
    }
}
