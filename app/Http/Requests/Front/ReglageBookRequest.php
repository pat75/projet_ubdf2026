<?php

namespace App\Http\Requests\Front;

use App\Services\Book\VueUltra2020;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Un reglage du book modifie depuis le mode edition (Ultra-frais,
 * Ultra-zen). Liste blanche : toute cle absente est refusee.
 * Les cles imbriquees du JSON s'ecrivent avec un point (nav_link.name_page).
 */
class ReglageBookRequest extends FormRequest
{
    /** Reglage => regles de sa valeur. */
    public static function reglages(): array
    {
        $texteCourt = ['nullable', 'string', 'max:120'];
        $html = ['nullable', 'string', 'max:5000'];
        $lien = ['nullable', 'string', 'max:255'];

        return [
            'theme' => ['required', Rule::in(['theme_white', 'theme_gris', 'theme_black'])],
            'visuel_size' => ['required', Rule::in(['small', 'normal', 'large'])],
            'header_size' => ['required', Rule::in(['S', 'M', 'L'])],
            'header' => ['required', 'integer', 'min:0', 'max:'.(count(VueUltra2020::ICONES) + 1)],
            'cursor' => ['required', Rule::in(['true', 'false'])],
            'titre' => $texteCourt,
            'description' => ['nullable', 'string', 'max:300'],
            'contact_titre' => ['nullable', 'string', 'max:200'],
            'nav_link.name_portfolio' => ['nullable', 'string', 'max:40'],
            'nav_link.name_page' => ['nullable', 'string', 'max:40'],
            'nav_link.name_contact' => ['nullable', 'string', 'max:40'],
            'footer' => $html,
            'contact_footer' => $html,
            'expert_css' => ['nullable', 'string', 'max:20000'],
            ...collect(VueUltra2020::RESEAUX)->mapWithKeys(fn ($r) => ["social_link.link_{$r}" => $lien])->all(),
        ];
    }

    /** Reglages dont la valeur est du HTML, filtree avant enregistrement. */
    public const HTML = ['footer', 'contact_footer'];

    public function rules(): array
    {
        $cle = (string) $this->input('cle');

        return [
            'cle' => ['required', 'string', Rule::in(array_keys(self::reglages()))],
            'valeur' => self::reglages()[$cle] ?? ['prohibited'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json(['erreur' => $validator->errors()->first()], 422));
    }
}
