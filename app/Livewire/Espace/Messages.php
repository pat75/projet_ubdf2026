<?php

namespace App\Livewire\Espace;

use App\Models\Conversation;
use App\Services\Messagerie\DetecteurSpamIA;
use App\Services\IA\CorrectionMessage;
use App\Services\Messagerie\Boite;
use App\Services\Messagerie\Intermediation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

/**
 * Demandes recues (ubaction__user_message du legacy). Le createur repond
 * ici sans passer par le lien de l'e-mail ; l'emetteur est prevenu de la
 * meme facon.
 *
 * Les demandes se rangent en deux dossiers : Contacts regroupe toutes
 * les demandes actives, quel que soit leur sujet au formulaire — prise
 * de contact, demande de travail similaire, achat d'une image —, et
 * Poubelle suit la suppression douce.
 */
class Messages extends Component
{
    use WithPagination;

    #[Url(as: 'dossier')]
    public string $dossier = 'contacts';

    #[Url(as: 'fil')]
    public ?int $ouvert = null;

    public string $reponse = '';

    /** Proposition de l'IA, en attente d'acceptation : ne remplace jamais $reponse toute seule. */
    public ?string $suggestionIA = null;

    public ?string $erreurIA = null;

    public bool $alerteEscroquerieMasquee = false;

    /** Demandes dont l'analyse a echoue pendant cette visite : on ne reessaie pas en boucle. */
    public array $analyseEchouee = [];

    /** Demandes analysees par appel : la liste se remplit au fil de l'eau, sans bloquer la page. */
    private const ANALYSES_PAR_APPEL = 3;

    /** Le cookie qui retient que l'utilisateur a ferme la note de securite. */
    private const COOKIE_ALERTE = 'espace_messages_alerte_masquee';

    protected Boite $boite;

    public function boot(Boite $boite): void
    {
        $this->boite = $boite;
    }

    public function mount(): void
    {
        $this->alerteEscroquerieMasquee = (bool) request()->cookie(self::COOKIE_ALERTE);
    }

    /** Ferme la note de securite pour de bon : un cookie l'annee suivante aussi. */
    public function fermerAlerte(): void
    {
        $this->alerteEscroquerieMasquee = true;
        Cookie::queue(Cookie::forever(self::COOKIE_ALERTE, '1'));
    }

    public function updatedDossier(): void
    {
        $this->resetPage();
        $this->ouvert = null;
    }

    public function choisir(string $dossier): void
    {
        $this->dossier = $dossier;
    }

    public function ouvrir(int $id): void
    {
        $this->boite->marquerLue($this->conversation($id));

        $this->ouvert = $id;
        $this->reset('reponse', 'suggestionIA', 'erreurIA');
    }

    /** Un clic sur la ligne la deplie, un second la replie. */
    public function basculer(int $id): void
    {
        $this->ouvert === $id ? $this->fermer() : $this->ouvrir($id);
    }

    public function fermer(): void
    {
        $this->ouvert = null;
    }

    public function repondre(Intermediation $intermediation): void
    {
        $this->validate(['reponse' => 'required|string|min:2|max:5000']);

        $conversation = $this->conversation((int) $this->ouvert);
        $intermediation->repondre($conversation, Intermediation::PROPRIETAIRE, $this->reponse, request()->ip());
        $intermediation->notifierAutrePartie($conversation, Intermediation::PROPRIETAIRE);

        $this->reset('reponse', 'suggestionIA', 'erreurIA');
    }

    /**
     * Propose une version corrigee de la reponse en cours de redaction.
     * N'ecrit jamais directement dans $reponse : voir utiliserSuggestionIA().
     */
    public function corrigerReponse(CorrectionMessage $correction): void
    {
        $this->validate(['reponse' => 'required|string|min:2|max:5000']);

        $this->erreurIA = null;

        // Chaque correction est facturee par OpenRouter.
        $cle = 'correction-ia|'.Auth::id();

        if (RateLimiter::tooManyAttempts($cle, 20)) {
            $this->erreurIA = __('Trop de corrections demandées, réessayez dans un moment.');

            return;
        }

        RateLimiter::hit($cle, 3600);

        try {
            $this->suggestionIA = $correction->corriger($this->reponse);
        } catch (RuntimeException $e) {
            $this->suggestionIA = null;
            $this->erreurIA = __("La correction IA n'est pas disponible pour le moment.");

            Log::warning('Correction IA en echec', ['message' => $e->getMessage()]);
        }
    }

    public function utiliserSuggestionIA(): void
    {
        if ($this->suggestionIA !== null) {
            $this->reponse = $this->suggestionIA;
        }

        $this->suggestionIA = null;
    }

    public function ignorerSuggestionIA(): void
    {
        $this->suggestionIA = null;
    }

    /**
     * Suppression douce, ou retour de la poubelle : le meme bouton fait
     * les deux, comme sur la maquette.
     */
    public function supprimer(int $id): void
    {
        $this->boite->basculerCorbeille($this->conversation($id));

        if ($this->ouvert === $id) {
            $this->ouvert = null;
        }
    }

    /** Supprime pour de bon toutes les demandes de la poubelle. */
    public function viderCorbeille(): void
    {
        $this->boite->viderCorbeille(Auth::user());

        $this->ouvert = null;
    }

    /**
     * Analyse IA des demandes de la page affichee qui ne l'ont pas encore
     * ete, quelques-unes a la fois. Declenchee apres l'affichage
     * (wire:init) : la liste apparait tout de suite, les badges
     * « Probable spam » suivent. Chaque resultat est garde en base, une
     * demande n'est analysee qu'une fois.
     */
    public function analyser(DetecteurSpamIA $detecteur): void
    {
        if (! $detecteur->actif() || $this->dossier === 'poubelle') {
            return;
        }

        $this->aAnalyser()->take(self::ANALYSES_PAR_APPEL)->get()
            ->each(function (Conversation $conversation) use ($detecteur) {
                if (! $detecteur->analyser($conversation)) {
                    $this->analyseEchouee[] = $conversation->id;
                }
            });
    }

    /** Demandes de la page courante jamais analysees (hors indesirables deja ecartes). */
    private function aAnalyser()
    {
        $ids = $this->requete()
            ->orderByDesc('last_message_at')->orderByDesc('id')
            ->forPage($this->getPage(), 14)->pluck('conversations.id');

        return Auth::user()->conversations()
            ->whereIn('id', $ids)
            ->where('is_spam', false)
            ->whereNull('spam_ia_probabilite')
            ->whereNotIn('id', $this->analyseEchouee);
    }

    /** Retire le signalement d'une demande, qu'il vienne de l'administration ou de l'IA. */
    public function rendreLegitime(int $id): void
    {
        $this->boite->rendreLegitime($this->conversation($id));
    }

    /**
     * Envoie a la poubelle toutes les demandes indesirables du dossier
     * Contacts : marquees par l'administration (`is_spam`) ou signalees
     * par l'IA (`spam_ia`). Restaurables depuis la poubelle.
     */
    public function supprimerIndesirables(): void
    {
        $this->boite->supprimerIndesirables(Auth::user());

        $this->ouvert = null;
        $this->resetPage();
    }

    public function render(): View
    {
        $dossiers = $this->dossiers();

        $fil = $this->ouvert ? $this->conversation($this->ouvert)->load('messages') : null;

        if ($fil) {
            $fil->setRelation('messages', $this->boite->sansDoublons($fil->messages));
        }

        return view('livewire.espace.messages', [
            'dossiers' => $dossiers,
            'dossierLibelle' => collect($dossiers)->firstWhere('cle', $this->dossier)['libelle'] ?? '',
            'totalNonLus' => collect($dossiers)->where('cle', '!=', 'poubelle')->sum('non_lus'),
            'conversations' => $this->requete()
                ->withCount(['messages as non_lus' => fn (Builder $q) => $q->where('from_owner', false)->whereNull('read_at')])
                ->orderByDesc('last_message_at')->orderByDesc('id')->paginate(14),
            'fil' => $fil,
            'analyseRestante' => app(DetecteurSpamIA::class)->actif() && $this->dossier !== 'poubelle'
                ? $this->aAnalyser()->count() : 0,
            'nbIndesirables' => $this->dossier === 'contacts' ? $this->boite->indesirables(Auth::user())->count() : 0,
        ]);
    }

    /**
     * Les deux dossiers, avec le nombre de demandes non lues dans
     * chacun — la poubelle n'affiche pas de pastille, comme dans la
     * maquette.
     *
     * @return array<int, array{cle: string, libelle: string, non_lus: int}>
     */
    private function dossiers(): array
    {
        return [
            ['cle' => 'contacts', 'libelle' => __('Contacts'), 'non_lus' => $this->boite->nonLues(Auth::user())],
            ['cle' => 'poubelle', 'libelle' => __('Supprimer'), 'non_lus' => 0],
        ];
    }

    private function requete()
    {
        return $this->boite->parDossier(Auth::user(), $this->dossier);
    }

    private function conversation(int $id): Conversation
    {
        return $this->boite->conversation(Auth::user(), $id);
    }

}
