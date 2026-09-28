<?php

namespace App\Http\Requests\Front;

use App\Services\Book\VueResponsive2014;
use App\Services\Book\VueUltra2020;
use App\Services\Book\VueZoom2016;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Un reglage du book modifie depuis le mode edition. Liste blanche par
 * modele (celui du createur) : toute cle absente est refusee.
 * Les cles imbriquees du JSON s'ecrivent avec un point (nav_link.name_page) ;
 * CHEMINS donne l'emplacement des cles Zoom rangees sous un sous-objet
 * (`link_bio.form_text`). Les cles `texte.*` vont dans les textes libres du
 * theme (theme_texts), pas dans sa configuration.
 */
class ReglageBookRequest extends FormRequest
{
    /** Reglages Zoom 2016 : cle => emplacement dans le JSON du theme. */
    public const CHEMINS = [
        'link_accueil' => ['link_accueil', 'form_text'],
        'link_bio' => ['link_bio', 'form_text'],
        'link_contact' => ['link_contact', 'form_text'],
        'ptf_activer_sociaux' => ['ptf_activer_sociaux', 'ptf_activer_sociaux'],
        'ptf_activer_contact' => ['ptf_activer_contact', 'ptf_activer_contact'],
        'ptf_activer_gmap' => ['ptf_activer_gmap', 'ptf_activer_gmap'],
        'ptf_activer_iso_category' => ['ptf_activer_iso_category', 'ptf_activer_iso_category'],
        // Responsive 2014
        'ub_menu_titre_accueil' => ['ub_menu_titre_accueil', 'form_text'],
        'ub_menu_titre_ptf' => ['ub_menu_titre_ptf', 'form_text'],
        'ub_menu_titre_actu' => ['ub_menu_titre_actu', 'form_text'],
        'ptf_type_presentation' => ['ptf_type_presentation', 'ptf_type_presentation'],
        'ptf_type_vign' => ['ptf_type_vign', 'ptf_type_vign'],
        'ptf_position_vign' => ['ptf_position_vign', 'ptf_position_vign'],
        'ptf_titre_aff' => ['ptf_titre_aff', 'ptf_titre_aff'],
        'ptf_vignette_aff' => ['ptf_vignette_aff', 'ptf_vignette_aff'],
    ];

    /** Reglage => regles de sa valeur, pour le modele du book. */
    public static function reglages(?string $theme = null): array
    {
        $communs = fn () => [
            'footer' => ['nullable', 'string', 'max:5000'],
            'expert_css' => ['nullable', 'string', 'max:20000'],
            ...collect(VueUltra2020::RESEAUX)->mapWithKeys(fn ($r) => ["social_link.link_{$r}" => ['nullable', 'string', 'max:255']])->all(),
        ];

        if (in_array($theme, ['mdl_2014_responsive', 'mdl_2015_classique'], true)) {
            $interrupteur = ['required', Rule::in(['true', 'false'])];
            $intitule = ['nullable', 'string', 'max:40'];

            return [
                'theme' => ['required', Rule::in(array_keys(VueResponsive2014::FONDS))],
                'ub_menu_titre_accueil' => $intitule,
                'ub_menu_titre_ptf' => $intitule,
                'ub_menu_titre_actu' => $intitule,
                'ptf_type_presentation' => ['required', Rule::in(['slide', 'full', 'image'])],
                'ptf_type_vign' => ['required', Rule::in(['thumbs', 'dots', 'none'])],
                'ptf_position_vign' => ['required', Rule::in(['top', 'bottom'])],
                'ptf_titre_aff' => $interrupteur,
                'ptf_vignette_aff' => $interrupteur,
                'texte.cont_menu_gauche' => ['nullable', 'string', 'max:5000'],
                'texte.cont_menu_gauche2' => ['nullable', 'string', 'max:5000'],
                'texte.cont_acceuil_bas' => ['nullable', 'string', 'max:5000'],
                ...$communs(),
            ];
        }

        if ($theme === 'mdl_2016_zoom') {
            $interrupteur = ['required', Rule::in(['true', 'false'])];
            $intitule = ['nullable', 'string', 'max:40'];

            return [
                'theme' => ['required', Rule::in(array_keys(VueZoom2016::FONDS))],
                'header' => ['required', 'integer', Rule::in([0, 1, ...array_map(fn (int $id) => $id + 2, array_keys(VueUltra2020::ICONES))])],
                'header_size' => ['required', Rule::in(['S', 'M', 'L'])],
                'visuel_size' => ['required', Rule::in(['small', 'normal', 'large'])],
                'footer' => ['nullable', 'string', 'max:5000'],
                'expert_css' => ['nullable', 'string', 'max:20000'],
                ...collect(VueUltra2020::RESEAUX)->mapWithKeys(fn ($r) => ["social_link.link_{$r}" => ['nullable', 'string', 'max:255']])->all(),
                'link_accueil' => $intitule,
                'link_bio' => $intitule,
                'link_contact' => $intitule,
                'ptf_activer_sociaux' => $interrupteur,
                'ptf_activer_contact' => $interrupteur,
                'ptf_activer_gmap' => $interrupteur,
                'ptf_activer_iso_category' => $interrupteur,
                'texte.cont_menu_gauche' => ['nullable', 'string', 'max:5000'],
                'texte.cont_menu_gauche2' => ['nullable', 'string', 'max:5000'],
            ];
        }

        $texteCourt = ['nullable', 'string', 'max:120'];
        $html = ['nullable', 'string', 'max:5000'];
        $lien = ['nullable', 'string', 'max:255'];

        return [
            'theme' => ['required', Rule::in(['theme_white', 'theme_gris', 'theme_black'])],
            'visuel_size' => ['required', Rule::in(['small', 'normal', 'large'])],
            'header_size' => ['required', Rule::in(['S', 'M', 'L'])],
            'header' => ['required', 'integer', Rule::in([0, 1, ...array_map(fn (int $id) => $id + 2, array_keys(VueUltra2020::ICONES))])],
            'cursor' => ['required', Rule::in(['true', 'false'])],
            'titre' => $texteCourt,
            'description' => ['nullable', 'string', 'max:300'],
            'contact_titre' => ['nullable', 'string', 'max:200'],
            'nav_link.name_portfolio' => ['nullable', 'string', 'max:40'],
            'nav_link.name_page' => ['nullable', 'string', 'max:40'],
            'nav_link.name_contact' => ['nullable', 'string', 'max:40'],
            'nav_link.name_projets' => ['nullable', 'string', 'max:40'],
            'footer' => $html,
            'contact_footer' => $html,
            'expert_css' => ['nullable', 'string', 'max:20000'],
            ...collect(VueUltra2020::RESEAUX)->mapWithKeys(fn ($r) => ["social_link.link_{$r}" => $lien])->all(),
        ];
    }

    /** Reglages dont la valeur est du HTML, filtree avant enregistrement. */
    public const HTML = ['footer', 'contact_footer', 'texte.cont_menu_gauche', 'texte.cont_menu_gauche2'];

    private function theme(): ?string
    {
        return $this->user()?->bookSetting?->theme;
    }

    public function rules(): array
    {
        $cle = (string) $this->input('cle');
        $reglages = self::reglages($this->theme());

        return [
            'cle' => ['required', 'string', Rule::in(array_keys($reglages))],
            'valeur' => $reglages[$cle] ?? ['prohibited'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json(['erreur' => $validator->errors()->first()], 422));
    }
}
