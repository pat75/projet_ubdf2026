<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\View\View;

class GalerieController extends Controller
{
    public function index(): View
    {
        return view('espace.galeries');
    }

    public function show(Gallery $galerie): View
    {
        return view('espace.visuels', ['galerie' => $galerie]);
    }
}
