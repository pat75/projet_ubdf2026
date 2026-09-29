<?php

namespace App\Livewire\Memo;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\Espace\CodeQr;
use App\Services\Memo\MemoBooks;
use App\Services\Messagerie\DepotDemande;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Page « Mon memo book » : les books mis de cote, une recherche par nom,
 * et le retrait d'un book. Servie au visiteur comme au creatif connecte.
 */
class Liste extends Component
{
    #[Url(as: 'q', except: '')]
    public string $recherche = '';

    /** Login du book dont la fenetre des messages est ouverte. */
    public ?string $messagesDe = null;

    /** Ouvre la fenetre des messages echanges avec ce book ; les messages recus passent lus. */
    public function ouvrirMessages(string $login, MemoBooks $memo): void
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);
        $book = User::query()->where('login', $login)->firstOrFail();

        Message::query()
            ->whereIn('conversation_id', $memo->conversations($proprietaire)->where('user_id', $book->id)->select('id'))
            ->where('from_owner', true)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $this->messagesDe = $book->login;
    }

    /** Login du book auquel on ecrit (fenetre « Ecrire »). */
    public ?string $ecrireA = null;

    public string $nomExpediteur = '';

    public string $messageTexte = '';

    public bool $messageEnvoye = false;

    /**
     * Ouvre la fenetre d'ecriture. Le nom est prerempli : celui du createur
     * connecte, ou le dernier nom donne par le visiteur dans une demande.
     */
    public function ouvrirEcriture(string $login, MemoBooks $memo): void
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);
        User::query()->where('login', $login)->firstOrFail();

        $this->resetValidation();
        $this->ecrireA = $login;
        $this->messageTexte = '';
        $this->messageEnvoye = false;
        $this->nomExpediteur = $proprietaire instanceof User
            ? $proprietaire->fullName()
            : ($proprietaire->fullName()
                ?? (string) Conversation::query()->where('sender_email', $proprietaire->email)->latest('id')->value('sender_name'));
    }

    /** Depose la demande comme le formulaire de contact du portail (DepotDemande). */
    public function envoyerMessage(MemoBooks $memo, DepotDemande $depot): void
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);
        $book = User::query()->where('login', (string) $this->ecrireA)->firstOrFail();

        $this->validate([
            'nomExpediteur' => ['required', 'string', 'min:2', 'max:100'],
            'messageTexte' => ['required', 'string', 'min:10', 'max:5000'],
        ], [], [
            'nomExpediteur' => __('nom'),
            'messageTexte' => __('message'),
        ]);

        if ($depot->limiteAtteinte((string) request()->ip())) {
            $this->addError('messageTexte', __('Trop de messages envoyés. Réessayez dans une heure.'));

            return;
        }

        $depot->deposer($book, [
            'action' => 'work_A_contact',
            'us_nom_prenom' => trim($this->nomExpediteur),
            'us_mail' => $proprietaire->email,
            'us_message' => trim($this->messageTexte),
        ], (string) request()->ip());

        $this->messageEnvoye = true;
    }

    public function fermerEcriture(): void
    {
        $this->ecrireA = null;
    }

    /** Active ou coupe le partage public de ce memoBook. */
    public function basculerPartage(MemoBooks $memo): void
    {
        $memo->basculerPartage($memo->proprietaire() ?? abort(401));
    }

    public function fermerMessages(): void
    {
        $this->messagesDe = null;
    }

    public function retirer(string $login, MemoBooks $memo): void
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);

        if ($book = User::query()->where('login', $login)->first()) {
            $memo->retirer($proprietaire, $book);
        }

        // Le compteur du menu suit.
        $this->dispatch('memo-change', total: $memo->compter($proprietaire));
    }

    public function render(MemoBooks $memo, CodeQr $qr): View
    {
        $proprietaire = $memo->proprietaire() ?? abort(401);

        $books = $memo->books($proprietaire, mb_substr($this->recherche, 0, 100));

        $bookMessages = $this->messagesDe
            ? User::query()->where('login', $this->messagesDe)->first()
            : null;

        $partage = $memo->partage($proprietaire);

        return view('livewire.memo.liste', [
            'bookEcriture' => $this->ecrireA ? User::query()->where('login', $this->ecrireA)->first() : null,
            'adresseExpediteur' => $proprietaire->email,
            'partage' => $partage?->actif ? $partage : null,
            'qrPartage' => $partage?->actif ? $qr->svg($partage->url(), 160) : null,
            'books' => $books,
            'total' => $memo->compter($proprietaire),
            'compteurs' => $memo->compteursMessages($proprietaire, $books->pluck('id')->all()),
            'bookMessages' => $bookMessages,
            'fils' => $bookMessages
                ? $memo->conversations($proprietaire)->where('user_id', $bookMessages->id)
                    ->with('messages')->orderByDesc('last_message_at')->get()
                : collect(),
        ]);
    }
}
