<?php

namespace App\Livewire\Espace;

use App\Models\Conversation;
use App\Services\Messagerie\Intermediation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Demandes recues (ubaction__user_message du legacy). Le createur repond
 * ici sans passer par le lien de l'e-mail ; l'emetteur est prevenu de la
 * meme facon.
 *
 * Les demandes se rangent en quatre dossiers : Contacts, Similaire et
 * Ventes suivent le sujet pose au formulaire (config('messagerie.demandes')),
 * Poubelle suit la suppression douce. Un sujet absent ou non reconnu —
 * 'autre', les demandes anciennes sans sujet — tombe dans Contacts, le
 * dossier general.
 */
class Messages extends Component
{
    use WithPagination;

    private const SIMILAIRE = 'work_B_similary';

    private const VENTES = 'work_C_buy';

    #[Url(as: 'dossier')]
    public string $dossier = 'contacts';

    #[Url(as: 'fil')]
    public ?int $ouvert = null;

    public string $reponse = '';

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
        $this->reset('reponse');
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

        $this->reset('reponse');
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

    public function render(): View
    {
        $dossiers = $this->dossiers();

        return view('livewire.espace.messages', [
            'dossiers' => $dossiers,
            'dossierLibelle' => collect($dossiers)->firstWhere('cle', $this->dossier)['libelle'] ?? '',
            'totalNonLus' => collect($dossiers)->where('cle', '!=', 'poubelle')->sum('non_lus'),
            'conversations' => $this->requete()
                ->withCount(['messages as non_lus' => fn (Builder $q) => $q->where('from_owner', false)->whereNull('read_at')])
                ->orderByDesc('last_message_at')->orderByDesc('id')->paginate(14),
            'fil' => $this->ouvert ? $this->conversation($this->ouvert)->load('messages') : null,
        ]);
    }

    /**
     * Les quatre dossiers, avec le nombre de demandes non lues dans
     * chacun — la poubelle n'affiche pas de pastille, comme dans la
     * maquette.
     *
     * @return array<int, array{cle: string, libelle: string, non_lus: int}>
     */
    private function dossiers(): array
    {
        $nonLus = fn ($q) => $q->whereHas('messages', fn (Builder $m) => $m->where('from_owner', false)->whereNull('read_at'));

        return [
            ['cle' => 'contacts', 'libelle' => __('Contacts'), 'non_lus' => $nonLus($this->parDossier('contacts'))->count()],
            ['cle' => 'similaire', 'libelle' => __('Similaire'), 'non_lus' => $nonLus($this->parDossier('similaire'))->count()],
            ['cle' => 'ventes', 'libelle' => __('Ventes'), 'non_lus' => $nonLus($this->parDossier('ventes'))->count()],
            ['cle' => 'poubelle', 'libelle' => __('Poubelle'), 'non_lus' => 0],
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
        $base = Auth::user()->conversations()->where('is_spam', false);

        return match ($dossier) {
            'poubelle' => $base->onlyTrashed(),
            'similaire' => $base->where('subject', self::SIMILAIRE),
            'ventes' => $base->where('subject', self::VENTES),
            default => $base->where(fn (Builder $q) => $q->whereNotIn('subject', [self::SIMILAIRE, self::VENTES])->orWhereNull('subject')),
        };
    }

    private function conversation(int $id): Conversation
    {
        return Auth::user()->conversations()->withTrashed()->where('is_spam', false)->findOrFail($id);
    }
}
