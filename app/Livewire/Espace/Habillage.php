<?php

namespace App\Livewire\Espace;

use App\Models\BookSetting;
use App\Services\Espace\ReglagesTheme;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Theme du book et ses reglages (user_pref_form et conf_modif_* du legacy).
 *
 * Le legacy avait un formulaire par theme. Ici le formulaire est deduit de
 * la configuration elle-meme : couleurs, cases, nombres et textes, dans
 * l'ordre ou le theme les declare.
 */
class Habillage extends Component
{
    public string $theme = '';

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

    public function enregistrer(ReglagesTheme $service): void
    {
        $this->validate([
            'titre' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'piedDePage' => 'nullable|string|max:2000',
            'valeurs' => 'array',
        ]);

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
            'title' => $this->titre,
            'description' => $this->description,
            'footer' => $this->piedDePage,
            'theme_settings' => $service->appliquer($configuration, $saisies),
        ]);

        session()->flash('statut', __('Habillage enregistré.'));
        $this->redirectRoute('espace.design');
    }

    public function render(ReglagesTheme $service): View
    {
        return view('livewire.espace.habillage', [
            'themes' => $this->themes(),
            'champs' => $service->champs($service->configuration($this->reglages(), $this->theme)),
        ]);
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
