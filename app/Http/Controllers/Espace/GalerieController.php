<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GalerieController extends Controller
{
    public function index(): View
    {
        return view('espace.galeries');
    }

    /** Les visuels se gerent desormais sur la page des portfolios. */
    public function show(Gallery $galerie): RedirectResponse
    {
        return redirect()->to(route('espace.galeries').'#portfolio-'.$galerie->id);
    }
}
