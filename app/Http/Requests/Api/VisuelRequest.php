<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** Textes d'un visuel, ou passage dans un autre portfolio. */
class VisuelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'titre' => ['sometimes', 'nullable', 'string', 'max:255'],
            'legende' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'portfolio_id' => ['sometimes', 'integer'],
        ];
    }
}
