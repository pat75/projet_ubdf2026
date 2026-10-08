<?php

namespace App\Livewire\Espace;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        'coaching' => 'coaching',
    ];

    public bool $web = true;

    public bool $portail = true;

    public bool $newsletter = true;

    public bool $disponible = false;

    public bool $analyse = false;

    public bool $coaching = true;

    public function mount(): void
    {
        $r = Auth::user()->bookSetting;

        $this->web = (bool) ($r?->diffuse_web ?? true);
        $this->portail = (bool) ($r?->diffuse_ub ?? true);
        $this->newsletter = (bool) ($r?->diffuse_newsletter ?? true);
        $this->disponible = (bool) ($r?->diffuse_availability ?? false);
        $this->analyse = (bool) ($r?->allow_ai_analysis ?? false);
        $this->coaching = (bool) ($r?->coaching ?? true);
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
            DB::table('media_tag')->whereIn('media_id', $ids)->delete();
            \App\Models\Media::withTrashed()->whereIn('id', $ids)->update([
                'ai_title' => null, 'ai_description' => null, 'ai_status' => null, 'ai_model' => null, 'analysed_at' => null,
            ]);
        }
    }

    /**
     * Retire un mot-cle IA de tous les visuels du creatif. Une analyse ne
     * repasse pas sur un visuel deja analyse : le mot ne revient pas.
     */
    public function supprimerMotCle(int $tag): void
    {
        DB::table('media_tag')
            ->where('tag_id', $tag)
            ->whereIn('media_id', Auth::user()->media()->select('id'))
            ->delete();
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
            // Mots-cles en francais seulement : ceux que le createur lit.
            'motsCles' => $this->analyse
                ? Tag::where('lang', 'fr')
                    ->whereHas('media', fn ($query) => $query->where('media.user_id', $user->id))
                    ->orderBy('label')->get(['id', 'label'])
                : collect(),
            // Conseils du Coach crea deja recus, du plus recent au plus ancien.
            'messagesCoach' => \App\Models\CoachMessage::where('user_id', $user->id)->where('statut', 'envoye')
                ->latest('envoye_le')->limit(20)->get(['id', 'objet', 'corps', 'envoye_le']),
            'demandeLe' => $this->demandeEnCours($user) ? $user->selection_requested_at : null,
            'canaux' => [
                'web' => [__('Diffusion sur internet'), __('Votre book est accessible à tous et référencé par les moteurs de recherche.'), $user->bookUrl()],
                'portail' => [__('Diffusion sur :marque', ['marque' => $marque]), __('Votre book apparaît dans l’annuaire et les recherches :marque.', ['marque' => $marque]), 'https://'.config('ubdf.portail_domain').'#'.$user->login],
                'newsletter' => [__('Newsletter'), __('Vos nouveaux projets peuvent être mis en avant dans la newsletter.'), null],
                'analyse' => [__('Indexation intelligente de mes visuels'), [
                    __('Nous analysons vos images pour leur attribuer des mots-clés.'),
                    __('Vos travaux apparaissent ainsi dans les recherches du site.'),
                    __('Ils sont aussi présentés, avec votre nom, sur des pages thématiques ouvertes aux moteurs de recherche.'),
                ], null],
            ],
        ]);
    }
}
