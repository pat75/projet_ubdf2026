<?php

namespace App\Http\Requests\Memo;

use Illuminate\Foundation\Http\FormRequest;

/** Ajout ou retrait d'un book du memo : son identifiant (login). */
class MemoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:50'],
        ];
    }
}
