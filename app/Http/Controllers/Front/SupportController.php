<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\SupportRequest;
use App\Mail\SupportNouveauMessage;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Visitor;
use App\Services\Messagerie\DetecteurSpam;
use App\Support\Marque;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Illuminate\View\View;

/** Page « Nous contacter » (/support) et fil d'un ticket, rouvert par lien signe. */
class SupportController extends Controller
{
    public function page(Request $request): View
    {
        return view('front.support', [
            'compte' => auth('web')->user(),
            // Page d'ou vient le clic sur le bouton flottant.
            'pageOrigine' => $request->headers->get('referer'),
        ]);
    }

    public function envoyer(SupportRequest $request, DetecteurSpam $spam): RedirectResponse
    {
        $compte = auth('web')->user();
        $email = $compte?->email ?? $request->validated('email');

        // Sans session, avec l'adresse d'un compte existant : rattachee a ce
        // compte, mais lisible seulement une fois connecte (identite non prouvee).
        $existants = $compte ? collect() : User::where('email', $email)->pluck('id');
        $compteRequis = ! $compte && ($existants->isNotEmpty() || Visitor::where('email', $email)->exists());

        $ticket = SupportTicket::create([
            'user_id' => $compte?->id ?? ($existants->count() === 1 ? $existants->first() : null),
            'email' => $compte ? null : $email,
            // Marque du site ou la demande est deposee : logo et objet des mails.
            'brand' => ($request->attributes->get('marque') ?? Marque::defaut())->code,
            'compte_requis' => $compteRequis,
            'page_origine' => $request->validated('page_origine'),
            'navigateur' => substr((string) $request->userAgent(), 0, 500),
            'ip' => $request->ip(),
            'is_spam' => ! $compte && $spam->estSuspecte($email, $request->ip(), $request->validated('message')),
        ]);
        $ticket->ajouter('client', $request->validated('message'), piece: $request->file('piece'));

        $this->prevenirAdmin($ticket);

        // Le numero du ticket, pas un simple drapeau : la confirmation sans
        // connexion ne vaut que pour la demande qui vient d'etre envoyee.
        return redirect()->to($ticket->lien())->with('envoye', $ticket->id);
    }

    public function fil(SupportTicket $ticket): View
    {
        // Juste apres l'envoi, la confirmation ne montre que le message
        // envoye : elle reste visible sans connexion.
        if (session('envoye') !== $ticket->id && ! $ticket->lisiblePar($this->compteConnecte())) {
            session()->put('url.intended', request()->fullUrl());
            session()->now('connexion_ouverte', true);

            return view('front.support-fil', ['ticket' => $ticket, 'verrou' => true]);
        }

        // Le lien signe ouvert vaut lecture : le menu « Message support » s'eteint.
        if (! $ticket->lu_client_at) {
            $ticket->update(['lu_client_at' => now()]);
        }

        return view('front.support-fil', ['ticket' => $ticket->load('messages', 'user')]);
    }

    public function repondre(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->lisiblePar($this->compteConnecte()), 403);

        // Discussion close : le formulaire n'est plus affiche, un envoi
        // direct ne la rouvre pas pour autant.
        if ($ticket->statut === 'clos') {
            return redirect()->to($ticket->lien());
        }

        // Piece jointe : seulement le createur du ticket, connecte.
        $createur = $ticket->user_id && auth('web')->id() === $ticket->user_id;
        $donnees = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
            'piece' => $createur ? SupportRequest::REGLE_PIECE : ['prohibited'],
        ]);

        $ticket->ajouter('client', $donnees['message'], piece: $request->file('piece'));
        $this->prevenirAdmin($ticket);

        return redirect()->to($ticket->lien())->with('envoye', $ticket->id);
    }

    /** Bouton « Résolu » : clot la discussion, avec la note facultative du client. */
    public function resoudre(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->lisiblePar($this->compteConnecte()), 403);

        $note = $request->validate(['note' => ['nullable', 'integer', 'between:1,5']])['note'] ?? null;

        $ticket->update(['statut' => 'clos', 'note' => $note, 'clos_at' => now()]);

        return redirect()->to($ticket->lien());
    }

    /** Piece jointe d'un message, par lien signe (fil client ou admin). */
    public function piece(SupportMessage $message): StreamedResponse
    {
        abort_unless($message->piece && Storage::disk('public')->exists($message->piece), 404);
        // L'admin connecte voit toutes les pieces (fiche de la demande).
        abort_unless(auth('admin')->check() || $message->ticket->lisiblePar($this->compteConnecte()), 403);

        return Storage::disk('public')->response($message->piece, $message->piece_nom, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            // Ouvert seul (PDF, image), le fichier ne peut executer aucun script.
            'Content-Security-Policy' => 'sandbox',
        ]);
    }

    private function compteConnecte(): User|Visitor|null
    {
        return auth('web')->user() ?? auth('visitor')->user();
    }

    private function prevenirAdmin(SupportTicket $ticket): void
    {
        if ($ticket->is_spam) {
            return;
        }

        // Envoi immediat (pas de worker requis) ; un SMTP en panne ne doit
        // pas faire perdre le message du client, deja enregistre.
        try {
            Mail::to(Marque::defaut()->email)->send(new SupportNouveauMessage($ticket));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
