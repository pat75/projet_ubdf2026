<?php

namespace App\Http\Requests\Front;

use App\Rules\CaptchaValide;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Message au support depuis /support. Un creatif connecte est deja
 * identifie ; sinon adresse mail et captcha sont obligatoires.
 */
class SupportRequest extends FormRequest
{
    /** Image ou PDF, 10 Mo : jamais de SVG (script embarque). */
    public const REGLE_PIECE = ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:10240'];

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $anonyme = ! auth('web')->check();

        return [
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'email' => [$anonyme ? 'required' : 'nullable', 'email:rfc', 'max:255'],
            'captcha' => $anonyme ? ['required', new CaptchaValide('support')] : ['nullable'],
            'page_origine' => ['nullable', 'string', 'max:500'],
            // Piece jointe : reservee au createur connecte (rangee dans son book).
            'piece' => $anonyme ? ['prohibited'] : self::REGLE_PIECE,
        ];
    }
}
