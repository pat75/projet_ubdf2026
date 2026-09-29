<?php

namespace App\Livewire\Visiteur;

use App\Models\Conversation;
use App\Models\Visitor;
use App\Services\IA\CorrectionMessage;
use App\Services\Memo\MemoBooks;
use App\Services\Messagerie\Intermediation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

/**
 * « Mes messages » d'un visiteur : les demandes qu'il a envoyees aux
 * creatifs et leurs reponses, sur le modele de la page des createurs
 * (App\Livewire\Espace\Messages), vue de l'autre cote du fil.
 *
 * Un fil appartient au visiteur par son adresse, confirmee
 * (MemoBooks::conversations()). Pas de poubelle : la suppression douce
 * d'une conversation est celle du createur, la poser ici la lui cacherait.
 */
class Messages extends Component
{
    use WithPagination;

    #[Url(as: 'fil')]
    public ?int $ouvert = null;

    public string $reponse = '';

    /** Proposition de l'IA, en attente d'acceptation : ne remplace jamais $reponse toute seule. */
    public ?string $suggestionIA = null;

    public ?string $erreurIA = null;

    /** Arrivee depuis le tableau de bord (?fil=) : l'echange s'ouvre deplie et passe lu. */
    public function mount(): void
    {
        if ($this->ouvert === null) {
            return;
        }

        $this->requete()->whereKey($this->ouvert)->exists() ? $this->ouvrir($this->ouvert) : $this->ouvert = null;
    }

    public function ouvrir(int $id): void
    {
        $conversation = $this->conversation($id);
        // Les messages du createur passent lus, comme dans le memo book.
        $conversation->messages()->where('from_owner', true)->whereNull('read_at')->update(['read_at' => now()]);

        $this->ouvert = $id;
        $this->reset('reponse', 'suggestionIA', 'erreurIA');
    }

    /** Un clic sur la ligne la deplie, un second la replie. */
    public function basculer(int $id): void
    {
        $this->ouvert === $id ? $this->ouvert = null : $this->ouvrir($id);
    }

    public function repondre(Intermediation $intermediation): void
    {
        $this->validate(['reponse' => 'required|string|min:2|max:5000']);

        $conversation = $this->conversation((int) $this->ouvert);
        $intermediation->repondre($conversation, Intermediation::EMETTEUR, $this->reponse, request()->ip());
        $intermediation->notifierAutrePartie($conversation, Intermediation::EMETTEUR);

        $this->reset('reponse', 'suggestionIA', 'erreurIA');
    }

    public function corrigerReponse(CorrectionMessage $correction): void
    {
        $this->validate(['reponse' => 'required|string|min:2|max:5000']);

        $this->erreurIA = null;

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

    public function render(): View
    {
        $fil = $this->ouvert ? $this->conversation($this->ouvert)->load('messages') : null;
        $nonLus = fn (Builder $q) => $q->where('from_owner', true)->whereNull('read_at');

        return view('livewire.visiteur.messages', [
            'visiteur' => $this->visiteur(),
            'totalNonLus' => $this->requete()->whereHas('messages', $nonLus)->count(),
            'conversations' => $this->requete()
                ->with('user')
                ->withCount(['messages as non_lus' => $nonLus])
                ->orderByDesc('last_message_at')->orderByDesc('id')->paginate(14),
            'fil' => $fil,
        ]);
    }

    private function visiteur(): Visitor
    {
        return auth('visitor')->user();
    }

    private function requete(): Builder
    {
        // Une demande mise a la poubelle par le createur reste visible ici.
        return app(MemoBooks::class)->conversations($this->visiteur())->withTrashed();
    }

    private function conversation(int $id): Conversation
    {
        return $this->requete()->findOrFail($id);
    }
}
