<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** Ordre voulu, du premier au dernier. Les identifiants etrangers au creatif sont ignores. */
class OrdreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'max:2000'],
            'ids.*' => ['integer'],
        ];
    }
}
