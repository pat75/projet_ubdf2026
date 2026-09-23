<?php

namespace App\Livewire\Espace;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

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

        session()->flash('statut', __('Informations enregistrées.'));
        $this->redirectRoute('espace.compte');
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

    public function render(): View
    {
        return view('livewire.espace.compte', [
            'creatif' => Auth::user(),
            'categories' => Category::where('is_active', true)->orderBy('position')->pluck('name', 'id'),
            'statuts' => config('ubdf.statuts'),
        ]);
    }
}
