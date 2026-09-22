<?php

namespace App\Livewire\Espace;

use App\Models\Conversation;
use App\Services\Messagerie\Intermediation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Demandes recues (ubaction__user_message du legacy). Le createur repond
 * ici sans passer par le lien de l'e-mail ; l'emetteur est prevenu de la
 * meme facon.
 */
class Messages extends Component
{
    use WithPagination;

    #[Url(as: 'fil')]
    public ?int $ouvert = null;

    public string $reponse = '';

    public function ouvrir(int $id): void
    {
        $conversation = $this->conversation($id);
        $conversation->messages()->where('from_owner', false)->whereNull('read_at')->update(['read_at' => now()]);

        $this->ouvert = $id;
        $this->reset('reponse');
    }

    public function repondre(Intermediation $intermediation): void
    {
        $this->validate(['reponse' => 'required|string|min:2|max:5000']);

        $conversation = $this->conversation((int) $this->ouvert);
        $intermediation->repondre($conversation, Intermediation::PROPRIETAIRE, $this->reponse, request()->ip());
        $intermediation->notifierAutrePartie($conversation, Intermediation::PROPRIETAIRE);

        $this->reset('reponse');
    }

    public function render(): View
    {
        return view('livewire.espace.messages', [
            'conversations' => Auth::user()->conversations()->where('is_spam', false)
                ->withCount(['messages as non_lus' => fn ($q) => $q->where('from_owner', false)->whereNull('read_at')])
                ->orderByDesc('last_message_at')->orderByDesc('id')->paginate(20),
            'fil' => $this->ouvert ? $this->conversation($this->ouvert)->load('messages') : null,
        ]);
    }

    private function conversation(int $id): Conversation
    {
        return Auth::user()->conversations()->where('is_spam', false)->findOrFail($id);
    }
}
