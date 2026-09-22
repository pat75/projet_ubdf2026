<?php

namespace App\Livewire\Espace;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/** Diffusion du book (ubaction__user_diffusion du legacy). */
class Diffusion extends Component
{
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

    public function enregistrer(): void
    {
        Auth::user()->bookSetting()->updateOrCreate([], [
            'diffuse_web' => $this->web,
            'diffuse_ub' => $this->portail,
            'diffuse_newsletter' => $this->newsletter,
            'diffuse_availability' => $this->disponible,
        ]);

        session()->flash('statut', __('Diffusion enregistrée.'));
        $this->redirectRoute('espace.diffusion');
    }

    public function render(): View
    {
        return view('livewire.espace.diffusion');
    }
}
