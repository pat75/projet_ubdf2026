<?php

namespace App\Services\Newsletter;

use App\Models\Campaign;
use App\Models\NewsletterMail;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Destinataires d'une newsletter : createurs abonnes et adresses inscrites
 * depuis le portail, fusionnes et dedoublonnes.
 *
 * Une adresse presente des deux cotes ne recoit le message qu'une fois, du
 * cote createur : c'est celui qui porte un prenom et un espace ou revenir
 * sur son choix.
 */
class Destinataires
{
    public const SOURCES = ['creatifs', 'visiteurs'];

    /** @return Collection<int, DestinataireNewsletter> */
    public function pour(Campaign $campagne): Collection
    {
        $marque = $campagne->brand ?: 'ub';
        $cibles = $campagne->cibles ?: [];

        $liste = collect();

        if (in_array('creatifs', $cibles, true)) {
            $liste = $liste->concat($this->creatifs($marque));
        }

        if (in_array('visiteurs', $cibles, true)) {
            $liste = $liste->concat($this->visiteurs($marque));
        }

        return $liste->unique(fn (DestinataireNewsletter $d) => mb_strtolower($d->email))->values();
    }

    public function compter(Campaign $campagne): int
    {
        return $this->pour($campagne)->count();
    }

    /**
     * Createurs de la marque qui acceptent la newsletter. Le refus se lit
     * dans `book_settings.diffuse_newsletter`, ou le lien de desabonnement
     * ecrit.
     *
     * @return Collection<int, DestinataireNewsletter>
     */
    public function creatifs(string $marque): Collection
    {
        return User::query()
            ->where('brand', $marque)
            ->whereNotNull('email')
            ->whereHas('bookSetting', fn ($q) => $q->where('diffuse_newsletter', true))
            ->get(['id', 'email', 'firstname'])
            ->map(fn (User $creatif) => new DestinataireNewsletter(
                email: $creatif->email,
                source: 'creatifs',
                prenom: $creatif->firstname,
                userId: $creatif->id,
            ));
    }

    /**
     * Adresses inscrites depuis le portail, comptes visiteurs compris : la
     * case « newsletter » de leur compte ecrit dans la meme table.
     *
     * @return Collection<int, DestinataireNewsletter>
     */
    public function visiteurs(string $marque): Collection
    {
        return NewsletterMail::query()
            ->where('brand', $marque)
            ->whereNull('desabonne_at')
            ->get(['id', 'email'])
            ->map(fn (NewsletterMail $inscrit) => new DestinataireNewsletter(
                email: $inscrit->email,
                source: 'visiteurs',
            ));
    }
}
