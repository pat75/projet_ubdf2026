<?php

namespace App\Http\Requests\Memo;

use App\Services\Memo\MemoBooks;
use Illuminate\Foundation\Http\FormRequest;

/** Selection du localStorage versee dans le memo en base. */
class FusionMemoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'logins' => ['present', 'array', 'max:'.MemoBooks::MAX],
            'logins.*' => ['string', 'max:50'],
        ];
    }
}
