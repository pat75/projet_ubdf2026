<?php

namespace App\Livewire\Espace;

use App\Livewire\Concerns\EnregistreChamps;
use App\Models\BookSetting;
use App\Models\User;
use App\Services\Espace\AffichageProfil;
use App\Services\Espace\DepotAvatar;
use App\Services\Espace\NettoyeurHtml;
use App\Services\Espace\ReglagesTheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Theme du book et ses reglages (user_pref_form et conf_modif_* du legacy).
 *
 * Le legacy avait un formulaire par theme. Ici le formulaire est deduit de
 * la configuration elle-meme : couleurs, cases, nombres et textes, dans
 * l'ordre ou le theme les declare.
 */
class Habillage extends Component
{
    use EnregistreChamps;
    use WithFileUploads;

    public string $theme = '';

    /** Photo de profil recadree en carre par le navigateur, en attente d'enregistrement. */
    public $avatarTemp = null;

    public string $titre = '';

    public string $description = '';

    public string $piedDePage = '';

    /** Valeurs des reglages, dans l'ordre de ReglagesTheme::champs(). */
    public array $valeurs = [];

    public function mount(ReglagesTheme $service): void
    {
        $reglages = $this->reglages();

        $this->theme = (string) $reglages->theme;
        $this->titre = (string) $reglages->title;
        $this->description = (string) $reglages->description;
        $this->piedDePage = (string) $reglages->footer;
        $this->chargerValeurs($service);
    }

    public function choisirTheme(string $theme, ReglagesTheme $service): void
    {
        abort_unless($this->themes()->has($theme), 422);

        $service->changerTheme($this->reglages(), $theme);
        $this->theme = $theme;
        $this->chargerValeurs($service);
    }

    public function deposerAvatar(DepotAvatar $service): void
    {
        $this->validate(['avatarTemp' => 'required|image|max:5120']);

        $service->deposer($this->reglages(), $this->avatarTemp);
        $this->avatarTemp = null;
        $this->annoncerAvatar();
    }

    public function retirerAvatar(DepotAvatar $service): void
    {
        $service->retirer($this->reglages());
        $this->annoncerAvatar();
    }

    /**
     * Les autres vignettes de la page (barre de tete, menu) sont hors du
     * composant : l'evenement les met a jour cote navigateur
     * (resources/js/espace.js, [data-avatar-profil]).
     */
    private function annoncerAvatar(): void
    {
        $creatif = Auth::user()->fresh('bookSetting');
        $profil = app(AffichageProfil::class);

        $this->dispatch('avatar-profil-modifie',
            url: $profil->photoUrl($creatif),
            initiales: $profil->initiales($creatif),
            couleur: $profil->couleur($creatif),
        );
    }

    public function enregistrer(ReglagesTheme $service): void
    {
        $this->validate(['valeurs' => 'array']);

        $reglages = $this->reglages();
        $configuration = $service->configuration($reglages, $this->theme);
        $champs = $service->champs($configuration);

        $saisies = [];
        foreach ($champs as $i => $champ) {
            if (array_key_exists($i, $this->valeurs)) {
                $saisies[$champ['chemin']] = $this->valeurs[$i];
            }
        }

        $reglages->update([
            'theme_settings' => $service->appliquer($configuration, $saisies),
        ]);

        session()->flash('statut', __('Habillage enregistré.'));
        $this->redirectRoute('espace.design');
    }

    protected function champsAutoEnregistres(): array
    {
        return [
            'titre' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'piedDePage' => 'nullable|string|max:2000',
        ];
    }

    protected function persisterChamp(string $nom, mixed $valeur): void
    {
        $colonne = ['titre' => 'title', 'description' => 'description', 'piedDePage' => 'footer'][$nom];

        // Le pied de page ressort sans echappement sur le book ({!! $pied !!}).
        $valeur = $nom === 'piedDePage' ? app(NettoyeurHtml::class)->nettoyer((string) $valeur) : (string) $valeur;

        $this->reglages()->update([$colonne => $valeur]);
        $this->{$nom} = (string) $valeur;
    }

    public function render(ReglagesTheme $service, AffichageProfil $profil): View
    {
        $creatif = Auth::user();

        return view('livewire.espace.habillage', [
            'themes' => $this->themes(),
            'champs' => $service->champs($service->configuration($this->reglages(), $this->theme)),
            'photoUrl' => $profil->photoUrl($creatif),
            'initiales' => $profil->initiales($creatif),
            'couleurAvatar' => $profil->couleur($creatif),
            'anciensModeles' => $this->anciensModelesProposes($creatif),
        ]);
    }

    /**
     * Les anciens modeles ne sont proposes qu'aux books crees
     * jusqu'en 2015, qui ont pu les connaitre — ou a un book qui en porte
     * encore un, pour qu'il le voie dans la liste.
     */
    private function anciensModelesProposes(User $creatif): bool
    {
        return ($creatif->created_at?->year ?? 0) <= 2015
            || ! in_array($this->theme, ['mdl_2020_ultra_frais', 'mdl_2020_ultra_zen', 'mdl_2015_grid', 'mdl_2016_zoom'], true);
    }

    private function chargerValeurs(ReglagesTheme $service): void
    {
        $this->valeurs = collect($service->champs($service->configuration($this->reglages(), $this->theme)))
            ->map(fn ($c) => $c['type'] === 'booleen' ? $c['valeur'] === 'true' : $c['valeur'])
            ->all();
    }

    private function reglages(): BookSetting
    {
        return Auth::user()->bookSetting()->firstOrCreate([], ['theme' => 'mdl_2014_responsive']);
    }

    private function themes()
    {
        return collect(config('book_themes'))->filter(fn ($t) => is_array($t))->map(fn ($t) => $t['titre']);
    }
}
