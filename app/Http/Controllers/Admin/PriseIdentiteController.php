<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * « Se connecter en tant que » : un administrateur ouvre l'espace d'un
 * createur pour voir exactement ce que celui-ci voit.
 *
 * Les deux gardes cohabitent : la session administrateur (`admin`) reste
 * ouverte pendant toute la prise d'identite, c'est elle qui autorise le
 * retour. On ne se deconnecte donc jamais du back-office pour entrer dans
 * un espace, et reprendre sa place ne redemande pas de mot de passe.
 *
 * La cle de session `prise_identite` sert de temoin : la barre rouge en
 * haut de l'espace s'affiche tant qu'elle est la.
 */
class PriseIdentiteController extends Controller
{
    public const SESSION = 'prise_identite';

    /** Page de relais : elle poste d'elle-meme vers `prendre`. */
    public function relais(User $creatif): View
    {
        return view('admin.prise-identite', ['creatif' => $creatif]);
    }

    public function prendre(Request $request, User $creatif): RedirectResponse
    {
        $administrateur = Auth::guard('admin')->user();

        abort_unless($administrateur !== null, 403);

        // Trace : c'est la seule action du back-office qui agit *au nom*
        // de quelqu'un d'autre, elle doit se retrouver dans le journal.
        AdminActivity::create([
            'admin_id' => $administrateur->getKey(),
            'admin_name' => $administrateur->name,
            'action' => 'prise_identite',
            'subject_type' => User::class,
            'subject_id' => (string) $creatif->getKey(),
            'subject_label' => $creatif->login,
            'ip' => $request->ip(),
        ]);

        $request->session()->put(self::SESSION, $administrateur->getKey());

        Auth::guard('web')->login($creatif);

        return redirect()->route('espace');
    }

    public function rendre(Request $request): RedirectResponse
    {
        abort_unless($request->session()->has(self::SESSION), 403);

        $request->session()->forget(self::SESSION);

        Auth::guard('web')->logout();

        return redirect('/admin/users');
    }
}
