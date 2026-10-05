<?php

namespace App\Livewire\Espace;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\IA\CorrectionMessage;
use App\Services\Messagerie\Intermediation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

    /** Le cookie qui retient que l'utilisateur a ferme la note de securite. */
    private const COOKIE_ALERTE = 'espace_messages_alerte_masquee';

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
        $conversation = $this->conversation($id);
        $conversation->messages()->where('from_owner', false)->whereNull('read_at')->update(['read_at' => now()]);

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
        $conversation = $this->conversation($id);

        $conversation->trashed() ? $conversation->restore() : $conversation->delete();

        if ($this->ouvert === $id) {
            $this->ouvert = null;
        }
    }

    /** Supprime pour de bon toutes les demandes de la poubelle. */
    public function viderCorbeille(): void
    {
        Auth::user()->conversations()->onlyTrashed()->get()
            ->each(fn (Conversation $conversation) => $conversation->forceDelete());

        $this->ouvert = null;
    }

    /** Retire le signalement d'une demande, qu'il vienne de l'administration ou de l'IA. */
    public function rendreLegitime(int $id): void
    {
        $this->conversation($id)->forceFill(['is_spam' => false, 'spam_ia' => false])->save();
    }

    /**
     * Envoie a la poubelle toutes les demandes indesirables du dossier
     * Contacts : marquees par l'administration (`is_spam`) ou signalees
     * par l'IA (`spam_ia`). Restaurables depuis la poubelle.
     */
    public function supprimerIndesirables(): void
    {
        $this->indesirables()->get()->each(fn (Conversation $conversation) => $conversation->delete());

        $this->ouvert = null;
        $this->resetPage();
    }

    public function render(): View
    {
        $dossiers = $this->dossiers();

        $fil = $this->ouvert ? $this->conversation($this->ouvert)->load('messages') : null;

        if ($fil) {
            $fil->setRelation('messages', $this->sansDoublons($fil->messages));
        }

        return view('livewire.espace.messages', [
            'dossiers' => $dossiers,
            'dossierLibelle' => collect($dossiers)->firstWhere('cle', $this->dossier)['libelle'] ?? '',
            'totalNonLus' => collect($dossiers)->where('cle', '!=', 'poubelle')->sum('non_lus'),
            'conversations' => $this->requete()
                ->withCount(['messages as non_lus' => fn (Builder $q) => $q->where('from_owner', false)->whereNull('read_at')])
                ->orderByDesc('last_message_at')->orderByDesc('id')->paginate(14),
            'fil' => $fil,
            'nbIndesirables' => $this->dossier === 'contacts' ? $this->indesirables()->count() : 0,
        ]);
    }

    /**
     * Un expediteur (ou le createur) qui renvoie plusieurs fois d'affilee
     * exactement le meme texte — un double clic sur « Envoyer », un souci
     * de connexion — n'affiche qu'une seule fois : seule une reponse au
     * contenu different rouvre une bulle.
     */
    private function sansDoublons(EloquentCollection $messages): EloquentCollection
    {
        $messages = $messages->values();

        return $messages->filter(function (Message $message, int $i) use ($messages) {
            $precedent = $messages->get($i - 1);

            return ! $precedent || $precedent->from_owner !== $message->from_owner || $precedent->body !== $message->body;
        })->values();
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
        // Un indesirable ne compte pas dans les non lus : la pastille ne
        // doit pas reclamer l'attention pour un spam.
        $nonLus = fn ($q) => $q->where('is_spam', false)->where(fn (Builder $q) => $q->where('spam_ia', false)->orWhereNull('spam_ia'))->whereHas('messages', fn (Builder $m) => $m->where('from_owner', false)->whereNull('read_at'));

        return [
            ['cle' => 'contacts', 'libelle' => __('Contacts'), 'non_lus' => $nonLus($this->parDossier('contacts'))->count()],
            ['cle' => 'poubelle', 'libelle' => __('Supprimer'), 'non_lus' => 0],
        ];
    }

    /**
     * `conversations()` est une relation HasMany : Eloquent la fait
     * passer pour un Builder une fois une premiere portee posee, mais son
     * type declare reste HasMany — d'ou l'absence de type de retour ici,
     * plutot qu'une signature que PHP rejetterait a l'execution.
     */
    private function requete()
    {
        return $this->parDossier($this->dossier);
    }

    private function parDossier(string $dossier)
    {
        // Les indesirables restent dans la liste, signales par un badge :
        // le createur doit les voir pour les supprimer (ou les garder).
        $base = Auth::user()->conversations();

        return match ($dossier) {
            'poubelle' => $base->onlyTrashed(),
            default => $base,
        };
    }

    private function conversation(int $id): Conversation
    {
        return Auth::user()->conversations()->withTrashed()->findOrFail($id);
    }

    /** Demandes indesirables du dossier Contacts (administration ou IA). */
    private function indesirables()
    {
        return $this->parDossier('contacts')
            ->where(fn (Builder $q) => $q->where('is_spam', true)->orWhere('spam_ia', true));
    }
}
