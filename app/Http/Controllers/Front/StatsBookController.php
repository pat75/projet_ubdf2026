<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Stats\CompteurVisites;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Pixel de comptage insere par les gabarits des books. */
class StatsBookController extends Controller
{
    private const GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

    public function __invoke(Request $request, string $login, CompteurVisites $compteur): Response
    {
        if (($book = User::where('login', $login)->first()) && ! $this->robot($request->userAgent())) {
            $compteur->compter($book, $request->user()?->id, (string) $request->ip(), $request->userAgent());
        }

        return response(base64_decode(self::GIF), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    private function robot(?string $ua): bool
    {
        return $ua === null || $ua === '' || preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|headless/i', $ua) === 1;
    }
}
