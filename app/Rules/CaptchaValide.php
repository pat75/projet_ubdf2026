<?php

namespace App\Rules;

use App\Services\Captcha\Captcha;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** Le code recopie correspond a l'image du captcha de ce formulaire (App\Services\Captcha\Captcha). */
class CaptchaValide implements ValidationRule
{
    public function __construct(private readonly string $formulaire) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(Captcha::class)->verifier($this->formulaire, is_string($value) ? $value : null)) {
            $fail(__('Le code recopié ne correspond pas à l’image.'));
        }
    }
}
