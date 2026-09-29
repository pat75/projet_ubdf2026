<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Visitor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * « Se connecter en tant que » pour un compte visiteur.
 *
 * Meme principe que pour un createur (PriseIdentiteController) : la
 * session `admin` reste ouverte, c'est elle qui autorise le retour, et le
 * geste est trace. Controleur distinct plutot que gardes melangees dans
 * un seul : les deux comptes vivent dans des tables et des gardes
 * differentes, et le temoin de session doit rester distinct pour que le
 * bandeau de retour sache d'ou l'on vient.
 */
class PriseIdentiteVisiteurController extends Controller
{
    public const SESSION = 'prise_identite_visiteur';

    /** Page de relais : elle poste d'elle-meme vers `prendre`. */
    public function relais(Visitor $visiteur): View
    {
        return view('admin.prise-identite-visiteur', ['visiteur' => $visiteur]);
    }

    public function prendre(Request $request, Visitor $visiteur): RedirectResponse
    {
        $administrateur = Auth::guard('admin')->user();

        abort_unless($administrateur !== null, 403);

        AdminActivity::create([
            'admin_id' => $administrateur->getKey(),
            'admin_name' => $administrateur->name,
            'action' => 'prise_identite',
            'subject_type' => Visitor::class,
            'subject_id' => (string) $visiteur->getKey(),
            'subject_label' => $visiteur->email,
            'ip' => $request->ip(),
        ]);

        $request->session()->put(self::SESSION, $administrateur->getKey());

        Auth::guard('visitor')->login($visiteur);

        return redirect()->route('visiteur.tableau');
    }

    public function rendre(Request $request): RedirectResponse
    {
        abort_unless($request->session()->has(self::SESSION), 403);

        $request->session()->forget(self::SESSION);

        Auth::guard('visitor')->logout();

        return redirect('/admin/visitors');
    }
}
