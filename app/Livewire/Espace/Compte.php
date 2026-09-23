<?php

namespace App\Livewire\Espace;

use App\Models\BillingProfile;
use App\Models\Category;
use App\Services\Facturation\AnnuaireEntreprises;
use App\Services\Facturation\SiretIntrouvable;
use App\Services\Facturation\SiretInvalide;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

/** Informations du compte et mot de passe (user_modif_form du legacy). */
class Compte extends Component
{
    /** Champs du profil modifiables ici, avec leur regle. */
    private const CHAMPS = [
        'firstname' => 'nullable|string|max:100',
        'lastname' => 'nullable|string|max:100',
        'company' => 'nullable|string|max:150',
        'address' => 'nullable|string|max:255',
        'zipcode' => 'nullable|string|max:20',
        'city' => 'nullable|string|max:100',
        'country' => 'nullable|string|max:100',
        'phone' => 'nullable|string|max:30',
        'mobile' => 'nullable|string|max:30',
        'website' => 'nullable|url:http,https|max:255',
        'facebook_url' => 'nullable|url:http,https|max:255',
        'instagram_url' => 'nullable|url:http,https|max:255',
        'twitter_url' => 'nullable|url:http,https|max:255',
    ];

    public array $profil = [];

    public ?int $categorie = null;

    public string $statut = '';

    public bool $sms = false;

    public string $email = '';

    public string $motDePasseActuel = '';

    public string $nouveauMotDePasse = '';

    public string $nouveauMotDePasse_confirmation = '';

    /** Identifiant recopie pour confirmer la suppression du portfolio. */
    public string $confirmationSuppression = '';

    /** Facturation electronique. */
    public bool $professionnel = false;

    public string $siret = '';

    public ?string $erreurSiret = null;

    /**
     * Copie des valeurs telles qu'elles sont en base : elle sert a savoir
     * si le formulaire a bouge, pour n'afficher la barre d'enregistrement
     * que lorsqu'il y a quelque chose a enregistrer.
     *
     * @var array<string, mixed>
     */
    public array $enregistre = [];

    public function mount(): void
    {
        $creatif = Auth::user();

        $this->profil = collect(self::CHAMPS)->mapWithKeys(fn ($r, $c) => [$c => (string) $creatif->{$c}])->all();
        $this->categorie = $creatif->category_id;
        $this->email = (string) $creatif->email;
        $this->sms = (bool) $creatif->accepts_sms;

        // Les fiches reprises portent parfois l'indice du legacy la ou on
        // attend un libelle : on ne propose alors rien plutot que « 6 ».
        $this->statut = in_array($creatif->status, config('ubdf.statuts'), true) ? (string) $creatif->status : '';

        $this->professionnel = $creatif->billingProfile()->exists();
        $this->siret = (string) $creatif->billingProfile?->siret;

        $this->enregistre = $this->valeurs();
    }

    /** @return array<string, mixed> */
    private function valeurs(): array
    {
        return [
            'profil' => $this->profil,
            'categorie' => $this->categorie,
            'statut' => $this->statut,
            'sms' => $this->sms,
        ];
    }

    /** La barre d'enregistrement n'apparait que si quelque chose a bouge. */
    #[Computed]
    public function modifie(): bool
    {
        return $this->valeurs() !== $this->enregistre;
    }

    /** Remet le formulaire dans l'etat de la base. */
    public function annuler(): void
    {
        foreach ($this->enregistre as $cle => $valeur) {
            $this->{$cle} = $valeur;
        }

        $this->resetValidation();
    }

    public function enregistrerProfil(): void
    {
        $this->validate(collect(self::CHAMPS)->mapWithKeys(fn ($r, $c) => ['profil.'.$c => $r])->all() + [
            'categorie' => ['nullable', Rule::exists('categories', 'id')],
            'statut' => ['nullable', Rule::in(config('ubdf.statuts'))],
        ]);

        Auth::user()->update(array_map(fn ($v) => $v === '' ? null : trim($v), $this->profil) + [
            'category_id' => $this->categorie,
            'status' => $this->statut ?: null,
            'accepts_sms' => $this->sms,
        ]);

        $this->enregistre = $this->valeurs();

        session()->flash('statut', __('Modifications enregistrées'));
    }

    /** Changer l'adresse ou le mot de passe demande le mot de passe actuel. */
    public function enregistrerAcces(): void
    {
        $creatif = Auth::user();

        $this->validate([
            'motDePasseActuel' => ['required', 'current_password'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($creatif->id)],
            'nouveauMotDePasse' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        $creatif->email = $this->email;

        if ($this->nouveauMotDePasse !== '') {
            $creatif->password = $this->nouveauMotDePasse;
        }

        $creatif->save();

        if ($this->nouveauMotDePasse !== '') {
            session()->regenerate();
        }

        $this->reset('motDePasseActuel', 'nouveauMotDePasse', 'nouveauMotDePasse_confirmation');
        session()->flash('statut', __('Accès enregistrés.'));
        $this->redirectRoute('espace.compte');
    }

    /*
     | Facturation electronique
     |
     | Le createur se declare professionnel, donne son SIRET, et les
     | informations legales sont relevees aupres de l'annuaire de l'Etat
     | plutot que saisies : c'est ce qui figurera sur ses factures.
     */

    public function basculerProfessionnel(): void
    {
        $this->professionnel = ! $this->professionnel;
        $this->erreurSiret = null;

        if (! $this->professionnel) {
            Auth::user()->billingProfile()->delete();
            $this->siret = '';

            session()->flash('statut', __('Facturation électronique désactivée.'));
        }
    }

    /*
     | Le numero part tout seul des que la saisie tient debout : quatorze
     | chiffres et la cle de Luhn juste. Tant qu'elle ne l'est pas — une
     | frappe en cours, une faute — on ne dit rien et on n'appelle pas
     | l'annuaire. Le message d'erreur reste reserve a un numero complet
     | mais faux, sinon il clignoterait a chaque caractere.
     */
    public function updatedSiret(string $valeur): void
    {
        $annuaire = app(AnnuaireEntreprises::class);
        $normalise = $annuaire->normaliser($valeur);

        $this->erreurSiret = null;

        if (strlen($normalise) < 14) {
            return;
        }

        // Deja releve : inutile de redemander a chaque retour sur le champ.
        if (Auth::user()->billingProfile()->where('siret', $normalise)->exists()) {
            return;
        }

        $this->verifierSiret($annuaire);
    }

    public function verifierSiret(AnnuaireEntreprises $annuaire): void
    {
        $this->erreurSiret = null;

        try {
            $donnees = $annuaire->parSiret($this->siret);
        } catch (SiretInvalide|SiretIntrouvable|RuntimeException $e) {
            $this->erreurSiret = $e->getMessage();

            return;
        }

        BillingProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $donnees + ['checked_at' => now()],
        );

        $this->siret = $donnees['siret'];

        session()->flash('statut', __('Informations d’entreprise récupérées.'));
    }

    /**
     * Suppression du portfolio, a la demande du createur.
     *
     * C'est une suppression douce : la fiche quitte le site, le book
     * n'est plus servi, mais les donnees restent en base le temps qu'un
     * administrateur puisse revenir dessus — le legacy posait de meme un
     * `us_delete` sans rien effacer. Le createur retape son identifiant :
     * c'est le garde-fou du geste.
     */
    public function supprimerPortfolio(): void
    {
        $creatif = Auth::user();

        $this->validate([
            'motDePasseActuel' => ['required', 'current_password'],
            'confirmationSuppression' => ['required', Rule::in([$creatif->login])],
        ], [
            'confirmationSuppression.in' => __('Recopiez votre identifiant pour confirmer la suppression.'),
        ]);

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $creatif->delete();

        $this->redirect(lien('home'));
    }

    public function render(): View
    {
        $creatif = Auth::user();

        return view('livewire.espace.compte', [
            'creatif' => $creatif,
            'facturation' => $creatif->billingProfile()->first(),
            'categories' => Category::where('is_active', true)->orderBy('position')->pluck('name', 'id'),
            'statuts' => config('ubdf.statuts'),
        ]);
    }
}
