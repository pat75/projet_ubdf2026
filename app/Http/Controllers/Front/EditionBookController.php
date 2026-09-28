<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ReglageBookRequest;
use App\Models\User;
use App\Services\Espace\NettoyeurHtml;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Mode edition des books passes en Blade (Ultra-frais / Ultra-zen, Zoom), ex-core_admin.js.
 *
 * La session du portail ne couvre pas les sous-domaines des books
 * (SESSION_DOMAIN vide) : le createur y entre par un lien signe, emis
 * depuis son espace, valable une minute et a usage unique (jeton en
 * cache, consomme a l'entree). La session ouverte sur le sous-domaine
 * ne sert qu'a son propre book.
 */
class EditionBookController extends Controller
{
    private const DUREE = 60;

    /** Portail, createur connecte : vers son book, en mode edition. */
    public function lien(Request $request): RedirectResponse
    {
        $creatif = $request->user();
        $jeton = Str::random(40);
        Cache::put('edition-book:'.$jeton, $creatif->getKey(), self::DUREE);

        return redirect()->away(URL::temporarySignedRoute('book.edition.entrer', now()->addSeconds(self::DUREE), [
            'login' => $creatif->login,
            'jeton' => $jeton,
        ]));
    }

    /** Sous-domaine du book : ouvre la session du createur, puis retire la signature de l'adresse. */
    public function entrer(Request $request, string $login, string $jeton): RedirectResponse
    {
        $id = Cache::pull('edition-book:'.$jeton);
        $creatif = $id ? User::find($id) : null;

        abort_unless($creatif && $creatif->login === $login, 403);

        Auth::login($creatif);
        $request->session()->regenerate();

        return redirect('/portfolio');
    }

    /** Un reglage modifie depuis le book : fusionne dans la configuration du theme. */
    public function enregistrer(ReglageBookRequest $request, string $login, NettoyeurHtml $nettoyeur): JsonResponse
    {
        $creatif = $request->user();
        abort_unless($creatif && $creatif->login === $login, 403);

        $cle = $request->validated('cle');
        $valeur = (string) $request->validated('valeur');

        $valeur = match (true) {
            in_array($cle, ReglageBookRequest::HTML, true) => $nettoyeur->nettoyer($valeur),
            // Injecte dans une balise <style> : on ne la laisse pas se refermer.
            $cle === 'expert_css' => str_ireplace('</style', '', $valeur),
            $cle === 'header' => (int) $valeur,
            default => trim(strip_tags($valeur)),
        };

        $reglages = $creatif->bookSetting()->firstOrCreate([]);

        // Textes libres du theme (presentation, contact...) : theme_texts.
        if (str_starts_with($cle, 'texte.')) {
            $textes = $reglages->theme_texts ?? [];
            $textes[$reglages->theme][substr($cle, 6)] = $valeur;
            $reglages->update(['theme_texts' => $textes]);

            return response()->json(['valeur' => $valeur]);
        }

        $conf = $reglages->theme_settings ?: json_decode(config('book_themes.'.$reglages->theme.'.defaut', '{}'), true);
        $chemin = ReglageBookRequest::CHEMINS[$cle] ?? explode('.', $cle);
        // Pas d'Arr::set : les cles du legacy contiennent des points (.ub_couleur_fond).
        $noeud = &$conf['data'];
        foreach ($chemin as $partie) {
            if (! isset($noeud[$partie]) || ! is_array($noeud[$partie])) {
                $noeud[$partie] = [];
            }
            $noeud = &$noeud[$partie];
        }
        $noeud = $valeur;
        unset($noeud);
        $reglages->update(['theme_settings' => $conf]);

        return response()->json(['valeur' => $valeur]);
    }
}
