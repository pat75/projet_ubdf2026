<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\NewsletterRequest;
use App\Models\NewsletterMail;
use Illuminate\Http\JsonResponse;

/** Inscription a la newsletter, remplace /front/action_ajax_2.php?action=add. */
class NewsletterController extends Controller
{
    public function __invoke(NewsletterRequest $request): JsonResponse
    {
        // Une adresse deja inscrite recoit le meme message : rien a divulguer.
        NewsletterMail::firstOrCreate(
            ['email' => $request->validated('mail')],
            [
                'brand' => $request->attributes->get('brand', 'ub'),
                'ip' => $request->ip(),
            ],
        )->update(['desabonne_at' => null]);

        return response()->json([
            'error' => false,
            'message' => __('Merci, votre inscription est enregistrée.'),
        ]);
    }
}
