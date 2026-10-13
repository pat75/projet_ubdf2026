<?php

namespace App\Services\Support;

use App\Models\SupportFiche;
use App\Models\SupportTicket;
use App\Services\IA\OpenRouterModelSelector;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Base de connaissances du support (RAG simple) : les fiches actives les
 * plus proches de la demande sont injectees dans le prompt qui redige une
 * proposition de reponse.
 *
 * ponytail: recherche FULLTEXT MySQL, pas d'embeddings ; passer a une
 * recherche vectorielle si les suggestions tombent a cote.
 */
class BaseConnaissance
{
    public function __construct(private readonly OpenRouterModelSelector $selecteur) {}

    /** @return Collection<int, SupportFiche> */
    public function rechercher(string $texte, int $limite = 4): Collection
    {
        $requete = SupportFiche::where('actif', true);

        if (SupportFiche::query()->getConnection()->getDriverName() === 'mysql') {
            return $requete->whereFullText(['question', 'reponse'], Str::limit($texte, 1000, ''))->limit($limite)->get();
        }

        // Repli (SQLite des tests) : une fiche qui partage un mot long.
        $mots = collect(preg_split('/\W+/u', Str::lower($texte)))->filter(fn ($m) => mb_strlen($m) > 4)->unique()->take(8);

        if ($mots->isEmpty()) {
            return collect();
        }

        return $requete->where(function ($q) use ($mots) {
            foreach ($mots as $mot) {
                $q->orWhere('question', 'like', "%{$mot}%")->orWhere('reponse', 'like', "%{$mot}%");
            }
        })->limit($limite)->get();
    }

    /** Proposition de reponse au dernier message du client, jamais envoyee seule. */
    public function suggerer(SupportTicket $ticket): string
    {
        $fil = $this->fil($ticket);
        $fiches = $this->rechercher($ticket->messages->where('auteur', 'client')->last()?->corps ?? $fil)
            ->map(fn (SupportFiche $f) => "Q : {$f->question}\nR : {$f->reponse}")
            ->implode("\n\n");

        $reponse = $this->selecteur->chatCompletions(
            messages: [
                ['role' => 'system', 'content' => "Tu es le support d'Ultra-book, plateforme de portfolios en ligne pour créatifs. ".
                    'Rédige en français la réponse au dernier message du client : courte, aimable, vouvoiement, sans formule de signature. '.
                    "Appuie-toi d'abord sur les fiches de la base de connaissances ; n'invente aucune fonctionnalité ni aucun tarif. ".
                    "Si l'information manque, dis que nous revenons vers lui rapidement.\n\nBase de connaissances :\n".($fiches ?: '(vide)')],
                ['role' => 'user', 'content' => $fil],
            ],
            costLevel: 2,
        );

        return trim((string) ($reponse['data']['choices'][0]['message']['content'] ?? ''));
    }

    /**
     * Tire du fil une fiche generique et anonymisee, inactive : un
     * administrateur la relit avant qu'elle ne serve. Rien si l'echange ne
     * contient rien de reutilisable.
     */
    public function proposerFiche(SupportTicket $ticket): ?SupportFiche
    {
        $reponse = $this->selecteur->chatCompletions(
            messages: [
                ['role' => 'system', 'content' => "À partir de cet échange avec le support d'Ultra-book, rédige une fiche de base de connaissances réutilisable : ".
                    'une question générique et la réponse correspondante, en français, sans aucun nom, adresse, identifiant ni donnée personnelle. '.
                    'Réponds uniquement en JSON : {"question": "...", "reponse": "..."}. '.
                    "Si l'échange ne contient rien de réutilisable (cas personnel, spam), réponds {}."],
                ['role' => 'user', 'content' => $this->fil($ticket)],
            ],
            costLevel: 1,
        );

        // Le modele entoure parfois le JSON de texte ou de ```json.
        $contenu = (string) ($reponse['data']['choices'][0]['message']['content'] ?? '');
        $debut = strpos($contenu, '{');
        $fiche = $debut === false ? null : json_decode(substr($contenu, $debut, strrpos($contenu, '}') - $debut + 1), true);

        if (empty($fiche['question']) || empty($fiche['reponse'])) {
            return null;
        }

        return SupportFiche::create([
            'question' => $fiche['question'],
            'reponse' => $fiche['reponse'],
            'support_ticket_id' => $ticket->id,
        ]);
    }

    private function fil(SupportTicket $ticket): string
    {
        return $ticket->messages
            ->map(fn ($m) => ($m->auteur === 'admin' ? 'Support' : 'Client').' : '.$m->corps)
            ->implode("\n\n");
    }
}
