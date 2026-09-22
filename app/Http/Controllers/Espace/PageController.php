<?php

namespace App\Http\Controllers\Espace;

use App\Http\Controllers\Controller;
use App\Models\BookArticle;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('espace.pages');
    }

    public function edit(BookArticle $page): View
    {
        Gate::authorize('update', $page->section);

        return view('espace.editeur-page', ['page' => $page]);
    }
}
