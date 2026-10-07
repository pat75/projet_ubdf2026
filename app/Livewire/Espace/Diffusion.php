<?php

namespace App\Livewire\Espace;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/** Diffusion du book (ubaction__user_diffusion du legacy). */
class Diffusion extends Component
{
    /** Interrupteur de la page => colonne de book_settings (liste blanche). */
    private const COLONNES = [
        'web' => 'diffuse_web',
        'portail' => 'diffuse_ub',
        'newsletter' => 'diffuse_newsletter',
        'disponible' => 'diffuse_availability',
        'analyse' => 'allow_ai_analysis',
    ];

    public bool $web = true;

    public bool $portail = true;

    public bool $newsletter = true;

    public bool $disponible = false;

    public bool $analyse = false;

    public function mount(): void
    {
        $r = Auth::user()->bookSetting;

        $this->web = (bool) ($r?->diffuse_web ?? true);
        $this->portail = (bool) ($r?->diffuse_ub ?? true);
        $this->newsletter = (bool) ($r?->diffuse_newsletter ?? true);
        $this->disponible = (bool) ($r?->diffuse_availability ?? false);
        $this->analyse = (bool) ($r?->allow_ai_analysis ?? false);
    }

    /** Un interrupteur s'enregistre des qu'il change, sans bouton (charte). */
    public function basculer(string $champ): void
    {
        abort_unless(isset(self::COLONNES[$champ]), 422);

        // Activer l'analyse IA demande une formule ; la couper reste toujours possible.
        if ($champ === 'analyse' && ! $this->analyse && ! Auth::user()->peutEtreAnalyseParIA()) {
            return;
        }

        $this->{$champ} = ! $this->{$champ};

        Auth::user()->bookSetting()->updateOrCreate([], [self::COLONNES[$champ] => $this->{$champ}]);

        // Accord retire : les resultats de l'analyse disparaissent avec lui.
        if ($champ === 'analyse' && ! $this->analyse) {
            $ids = Auth::user()->media()->withTrashed()->pluck('id');
            \Illuminate\Support\Facades\DB::table('media_tag')->whereIn('media_id', $ids)->delete();
            \App\Models\Media::withTrashed()->whereIn('id', $ids)->update([
                'ai_title' => null, 'ai_description' => null, 'ai_status' => null, 'ai_model' => null, 'analysed_at' => null,
            ]);
        }
    }

    /** Demande a figurer dans la selection (us_formule_ask_date du legacy). */
    public function demanderSelection(): void
    {
        $user = Auth::user();

        if ($user->in_home_selection || $this->demandeEnCours($user)) {
            return;
        }

        $user->forceFill(['selection_requested_at' => now()])->saveQuietly();
    }

    /** Au-dela, une demande restee sans suite peut etre renouvelee. */
    public const DELAI_DEMANDE_JOURS = 30;

    /**
     * Une demande est « en cours d'examen » pendant DELAI_DEMANDE_JOURS
     * jours, tant que l'equipe n'a pas mis le book en selection apres elle.
     */
    private function demandeEnCours($user): bool
    {
        $demande = $user->selection_requested_at;

        if (! $demande || $demande->lt(now()->subDays(self::DELAI_DEMANDE_JOURS))) {
            return false;
        }

        return ! $user->home_selection_at || $user->home_selection_at->lt($demande);
    }

    public function render(): View
    {
        $user = Auth::user();
        // Les requetes Livewire ne passent pas par ResoudreMarque : la marque du compte.
        $marque = \App\Support\Marque::depuisCode($user->brand ?: 'ub')->nom;

        return view('livewire.espace.diffusion', [
            'enSelection' => (bool) $user->in_home_selection,
            'analyseDisponible' => $user->peutEtreAnalyseParIA(),
            'demandeLe' => $this->demandeEnCours($user) ? $user->selection_requested_at : null,
            'canaux' => [
                'web' => [__('Diffusion sur internet'), __('Votre book est accessible à tous et référencé par les moteurs de recherche.'), $user->bookUrl()],
                'portail' => [__('Diffusion sur :marque', ['marque' => $marque]), __('Votre book apparaît dans l’annuaire et les recherches :marque.', ['marque' => $marque]), 'https://'.config('ubdf.portail_domain').'#'.$user->login],
                'newsletter' => [__('Newsletter'), __('Vos nouveaux projets peuvent être mis en avant dans la newsletter.'), null],
                'analyse' => [__('Indexation intelligente de mes visuels'), [
                    __('Nous analysons vos images pour leur attribuer des mots-clés.'),
                    __('Vos travaux apparaissent ainsi dans les recherches du site.'),
                ], null],
            ],
        ]);
    }
}
