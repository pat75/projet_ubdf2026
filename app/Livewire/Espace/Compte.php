<?php

namespace App\Livewire\Espace;

use App\Livewire\Concerns\EnregistreChamps;
use App\Models\BillingProfile;
use App\Models\Category;
use App\Services\Espace\ArchiveBook;
use App\Services\Facturation\AnnuaireEntreprises;
use App\Services\Facturation\SiretIntrouvable;
use App\Services\Facturation\SiretInvalide;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use RuntimeException;

/** Informations du compte et mot de passe (user_modif_form du legacy). */
class Compte extends Component
{
    use EnregistreChamps;

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

    /** Abonnement a la newsletter (book_settings.diffuse_newsletter). */
    public bool $newsletter = false;


    public string $motDePasseActuel = '';

    /** Identifiant recopie pour confirmer la suppression du portfolio. */
    public string $confirmationSuppression = '';

    /** Facturation electronique. */
    public bool $professionnel = false;

    public string $siret = '';

    public ?string $erreurSiret = null;

    public function mount(): void
    {
        $creatif = Auth::user();

        $this->profil = collect(self::CHAMPS)->mapWithKeys(fn ($r, $c) => [$c => (string) $creatif->{$c}])->all();
        $this->categorie = $creatif->category_id;
        $this->sms = (bool) $creatif->accepts_sms;
        $this->newsletter = (bool) $creatif->bookSetting()->value('diffuse_newsletter');

        // Les fiches reprises portent parfois l'indice du legacy la ou on
        // attend un libelle : on ne propose alors rien plutot que « 6 ».
        $this->statut = in_array($creatif->status, config('ubdf.statuts'), true) ? (string) $creatif->status : '';

        $this->professionnel = $creatif->billingProfile()->exists();
        $this->siret = (string) $creatif->billingProfile?->siret;
    }

    /*
     | Chaque champ s'enregistre seul (edition sur place, voir
     | Claude_design.md). Les textes du profil, l'adresse mail et le mot de
     | passe passent tous par enregistrerChamp() ; les listes et
     | l'interrupteur SMS, des qu'ils changent. « motDePasse » n'est pas
     | une propriete du composant : rien ne le lit jamais en retour, un mot
     | de passe ne s'affiche pas en clair.
     */

    protected function champsAutoEnregistres(): array
    {
        return self::CHAMPS + [
            // L'un comme l'autre servent a se connecter, mais la session en
            // cours prouve deja l'identite : pas besoin de reprouver le mot
            // de passe actuel pour les changer, contrairement a une action
            // plus lourde (supprimer le portfolio).
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(Auth::id())],
            'motDePasse' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }

    protected function persisterChamp(string $nom, mixed $valeur): void
    {
        if ($nom === 'email') {
            Auth::user()->update(['email' => $valeur]);

            return;
        }

        if ($nom === 'motDePasse') {
            if ($valeur === '') {
                return;
            }

            Auth::user()->update(['password' => $valeur]);
            session()->regenerate();

            return;
        }

        $valeur = trim((string) $valeur);

        Auth::user()->update([$nom => $valeur === '' ? null : $valeur]);
        $this->profil[$nom] = $valeur;
    }

    public function updatedCategorie(): void
    {
        $this->validate(['categorie' => ['nullable', Rule::exists('categories', 'id')]]);

        Auth::user()->update(['category_id' => $this->categorie ?: null]);
    }

    public function updatedStatut(): void
    {
        $this->validate(['statut' => ['nullable', Rule::in(config('ubdf.statuts'))]]);

        Auth::user()->update(['status' => $this->statut ?: null]);
    }

    public function updatedSms(): void
    {
        Auth::user()->update(['accepts_sms' => $this->sms]);
    }

    /** Meme reglage que le lien de desabonnement des campagnes. */
    public function updatedNewsletter(): void
    {
        Auth::user()->bookSetting()->updateOrCreate([], ['diffuse_newsletter' => $this->newsletter]);
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
        $this->dispatch('siret-trouve');
    }

    /**
     * Suppression du portfolio, a la demande du createur.
     *
     * C'est une suppression douce : la fiche quitte le site, le book
     * n'est plus servi, mais les donnees restent en base le temps qu'un
     * administrateur puisse revenir dessus — le legacy posait de meme un
     * `us_delete` sans rien effacer. Le createur retape son identifiant :
     * c'est le garde-fou du geste. Les images, elles, quittent le disque
     * public : elles sont compressees dans storage/books_effaces/.
     */
    public function supprimerPortfolio(ArchiveBook $archive): void
    {
        $creatif = Auth::user();

        $this->validate([
            'motDePasseActuel' => ['required', 'current_password'],
            'confirmationSuppression' => ['required', Rule::in([$creatif->login])],
        ], [
            'confirmationSuppression.in' => __('Recopiez votre identifiant pour confirmer la suppression.'),
        ]);

        $archive->archiver($creatif);

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
