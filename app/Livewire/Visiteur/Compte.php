<?php

namespace App\Livewire\Visiteur;

use App\Actions\Visiteur\CreerCompteVisiteur;
use App\Livewire\Concerns\EnregistreChamps;
use App\Mail\BienvenueVisiteur;
use App\Models\NewsletterMail;
use App\Models\Visitor;
use App\Support\Marque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/**
 * « Mon compte » d'un visiteur, sur le modele de celui des createurs
 * (App\Livewire\Espace\Compte) : mot de passe, adresse mail, prenom et nom
 * modifiables sur place, newsletter, suppression du compte.
 */
class Compte extends Component
{
    use EnregistreChamps;

    public bool $newsletter = false;

    public string $motDePasseActuel = '';

    public function mount(): void
    {
        $this->newsletter = NewsletterMail::query()
            ->where('email', $this->visiteur()->email)
            ->whereNull('desabonne_at')
            ->exists();
    }

    protected function champsAutoEnregistres(): array
    {
        return [
            'firstname' => 'nullable|string|max:100',
            'lastname' => 'nullable|string|max:100',
            // Unique chez les visiteurs comme chez les createurs : a la
            // connexion, l'adresse doit designer un seul compte.
            'email' => ['required', 'email', 'max:255',
                Rule::unique('visitors', 'email')->ignore($this->visiteur()->id),
                Rule::unique('users', 'email')->whereNull('deleted_at')],
            'motDePasse' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    protected function persisterChamp(string $nom, mixed $valeur): void
    {
        $visiteur = $this->visiteur();

        if ($nom === 'motDePasse') {
            if ($valeur !== '') {
                $visiteur->update(['password' => $valeur]);
                session()->regenerate();
            }

            return;
        }

        if ($nom === 'email') {
            $this->changerAdresse($visiteur, mb_strtolower(trim((string) $valeur)));

            return;
        }

        $valeur = trim((string) $valeur);
        $visiteur->update([$nom => $valeur === '' ? null : $valeur]);
    }

    /**
     * Nouvelle adresse : elle repasse non confirmee, et un lien part a la
     * nouvelle. Les messages envoyes avec elle ne s'affichent qu'une fois
     * confirmee (MemoBooks::conversations) : sans cela, prendre l'adresse
     * d'un tiers suffirait a lire ses echanges. L'inscription newsletter
     * suit l'adresse.
     */
    private function changerAdresse(Visitor $visiteur, string $adresse): void
    {
        if ($adresse === $visiteur->email) {
            return;
        }

        NewsletterMail::query()->where('email', $visiteur->email)->update(['email' => $adresse]);

        $visiteur->forceFill(['email' => $adresse, 'email_verified_at' => null])->save();

        Mail::to($adresse)->send(new BienvenueVisiteur(
            $visiteur,
            Marque::depuisCode($visiteur->brand ?: 'ub'),
            app(CreerCompteVisiteur::class)->lienConfirmation($visiteur),
        ));
    }

    public function updatedNewsletter(): void
    {
        $visiteur = $this->visiteur();

        if ($this->newsletter) {
            NewsletterMail::query()->firstOrCreate(
                ['email' => $visiteur->email],
                ['brand' => $visiteur->brand ?: 'ub', 'ip' => request()->ip()],
            )->update(['desabonne_at' => null]);
        } else {
            NewsletterMail::query()->where('email', $visiteur->email)->delete();
        }
    }

    /**
     * Suppression definitive, apres le mot de passe actuel : le memoBook,
     * les visites et le partage public partent avec le compte (cles
     * etrangeres en cascade), l'inscription newsletter aussi.
     */
    public function supprimerCompte(): void
    {
        $this->validate(['motDePasseActuel' => ['required', 'current_password:visitor']]);

        $visiteur = $this->visiteur();

        Auth::guard('visitor')->logout();
        session()->invalidate();
        session()->regenerateToken();

        NewsletterMail::query()->where('email', $visiteur->email)->delete();
        $visiteur->forceDelete();

        $this->redirect(lien('home'));
    }

    public function render(): View
    {
        return view('livewire.visiteur.compte', ['visiteur' => $this->visiteur()]);
    }

    private function visiteur(): Visitor
    {
        return auth('visitor')->user() ?? abort(401);
    }
}
