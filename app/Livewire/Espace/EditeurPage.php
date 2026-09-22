<?php

namespace App\Livewire\Espace;

use App\Models\BookArticle;
use App\Services\Espace\NettoyeurHtml;
use Illuminate\View\View;
use Livewire\Component;

/** Edition d'une page de texte (ajax_2014_usadmin_editxt.php du legacy). */
class EditeurPage extends Component
{
    public BookArticle $page;

    public string $titre = '';

    public string $corps = '';

    public bool $enLigne = false;

    public function mount(BookArticle $page): void
    {
        $this->authorize('update', $page->section);

        $this->page = $page;
        $this->titre = (string) $page->title;
        $this->corps = (string) $page->body;
        $this->enLigne = $page->status === 'published';
    }

    public function enregistrer(NettoyeurHtml $nettoyeur): void
    {
        $this->validate([
            'titre' => 'required|string|max:255',
            'corps' => 'nullable|string|max:200000',
        ]);

        $this->corps = $nettoyeur->nettoyer($this->corps);

        $this->page->update([
            'title' => $this->titre,
            'body' => $this->corps,
            'status' => $this->enLigne ? 'published' : 'draft',
        ]);

        session()->flash('statut', __('Page enregistrée.'));
        $this->redirectRoute('espace.pages');
    }

    public function supprimer(): void
    {
        $this->page->delete();
        $this->redirectRoute('espace.pages');
    }

    public function render(): View
    {
        return view('livewire.espace.editeur-page');
    }
}
