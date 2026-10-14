<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** Un visuel par requete : une image envoyee, ou un lien YouTube/Vimeo. */
class DepotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fichier' => ['required_without:video', 'prohibits:video', 'image', 'mimes:jpg,jpeg,png,gif', 'max:'.config('images.envoi_ko_max')],
            'video' => ['required_without:fichier', 'string', 'url', 'max:500'],
        ];
    }
}
