<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnuaireController extends Controller
{
    /** Annuaire alphabetique des creatifs inscrits a l'annuaire public. */
    public function index(Request $request, ?string $lettre = null): View
    {
        $brand = $request->attributes->get('brand', 'ub');

        $creatifs = User::query()
            ->with('category')
            ->where('brand', $brand)
            ->where('in_home_selection', true)
            ->where('in_directory', true)
            ->when($lettre, fn ($query) => $query->where('login', 'like', $lettre.'%'))
            ->orderBy('login')
            ->paginate(60)
            ->withQueryString();

        return view('front.annuaire', [
            'creatifs' => $creatifs,
            'lettre' => $lettre,
            'lettres' => array_merge(range('a', 'z'), range('0', '9')),
        ]);
    }
}
