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
    ];

    public bool $web = true;

    public bool $portail = true;

    public bool $newsletter = true;

    public bool $disponible = false;

    public function mount(): void
    {
        $r = Auth::user()->bookSetting;

        $this->web = (bool) ($r?->diffuse_web ?? true);
        $this->portail = (bool) ($r?->diffuse_ub ?? true);
        $this->newsletter = (bool) ($r?->diffuse_newsletter ?? true);
        $this->disponible = (bool) ($r?->diffuse_availability ?? false);
    }

    /** Un interrupteur s'enregistre des qu'il change, sans bouton (charte). */
    public function basculer(string $champ): void
    {
        abort_unless(isset(self::COLONNES[$champ]), 422);

        $this->{$champ} = ! $this->{$champ};

        Auth::user()->bookSetting()->updateOrCreate([], [self::COLONNES[$champ] => $this->{$champ}]);
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.espace.diffusion', [
            'canaux' => [
                'web' => [__('Diffusion sur internet'), __('Votre book est accessible à tous et référencé par les moteurs de recherche.'), $user->bookUrl()],
                'portail' => [__('Diffusion sur Ultra-book'), __('Votre book apparaît dans l’annuaire et les recherches Ultra-book.'), 'https://'.config('ubdf.portail_domain').'#'.$user->login],
                'newsletter' => [__('Newsletter'), __('Vos nouveaux projets peuvent être mis en avant dans la newsletter.'), null],
            ],
        ]);
    }
}
