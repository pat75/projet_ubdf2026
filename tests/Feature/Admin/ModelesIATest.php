<?php

use App\Filament\Pages\ModelesIA;
use App\Models\Admin;
use App\Models\Reglage;
use App\Services\IA\CatalogueOpenRouter;
use App\Services\IA\OpenRouterModelSelector;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

function catalogue(): void
{
    Http::fake(['openrouter.ai/api/v1/models' => Http::response(['data' => [
        ['id' => 'a/texte', 'architecture' => ['input_modalities' => ['text']], 'pricing' => ['prompt' => '0.0000001', 'completion' => '0.0000004']],
        ['id' => 'a/texte-lite-2', 'architecture' => ['input_modalities' => ['text']], 'pricing' => ['prompt' => '0.0000001', 'completion' => '0.0000004']],
        ['id' => 'a/texte-lite-pro', 'architecture' => ['input_modalities' => ['text']], 'pricing' => ['prompt' => '0.00001', 'completion' => '0.00004']],
        ['id' => 'a/texte-lite-3', 'architecture' => ['input_modalities' => ['text']], 'pricing' => ['prompt' => '0.000005', 'completion' => '0.00002']],
        ['id' => 'b/vision', 'architecture' => ['input_modalities' => ['text', 'image']], 'pricing' => ['prompt' => '0', 'completion' => '0']],
    ]])]);
}

it('juge chaque modele contre le catalogue', function () {
    catalogue();
    $c = app(CatalogueOpenRouter::class);

    expect($c->etat('a/texte', 'text'))->toMatchArray(['valide' => true, 'prix' => '$0.1 / $0.4 par M tokens'])
        ->and($c->etat('b/vision', 'vision'))->toMatchArray(['valide' => true, 'prix' => 'gratuit'])
        ->and($c->etat('a/texte', 'vision')['valide'])->toBeFalse()
        ->and($c->etat('x/retire', 'text')['valide'])->toBeFalse();
});

it('ne conclut rien si le catalogue est injoignable', function () {
    Http::fake(['*' => Http::response('', 500)]);

    expect(app(CatalogueOpenRouter::class)->etat('a/texte', 'text')['valide'])->toBeNull();
});

it('prend les listes enregistrees, sinon les defauts', function () {
    expect(OpenRouterModelSelector::listes())->toBe(OpenRouterModelSelector::DEFAUTS);

    Reglage::definirJson(Reglage::MODELES_IA, ['text' => [1 => ['a/texte']], 'vision' => [1 => ['b/vision']]]);

    expect(OpenRouterModelSelector::listes()['text'][1])->toBe(['a/texte']);
});

it('enregistre les listes depuis la page', function () {
    catalogue();
    Repeater::fake();
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');

    Livewire::test(ModelesIA::class)
        ->assertOk()
        ->set('data.text.1', [['id' => 'a/texte']])
        ->set('data.vision.1', [['id' => 'b/vision']])
        ->call('save')
        ->assertHasNoErrors();

    expect(OpenRouterModelSelector::listes()['text'][1])->toBe(['a/texte'])
        ->and(OpenRouterModelSelector::listes()['vision'][1])->toBe(['b/vision']);
});

it('propose un remplacant de la meme famille au cout approchant', function () {
    catalogue();
    $c = app(CatalogueOpenRouter::class);

    // Famille « texte lite » ; au prix de 0,1 $/M, le moins cher des deux.
    expect($c->suggestion('a/texte-lite-1', 'text', [], 0.1))->toBe('a/texte-lite-2')
        // Au prix de 5 $/M, celui qui coute 5 $/M.
        ->and($c->suggestion('a/texte-lite-1', 'text', [], 5.0))->toBe('a/texte-lite-3')
        // Rien chez un autre fournisseur, ni en vision pour un modele texte.
        ->and($c->suggestion('z/texte-lite-1', 'text'))->toBeNull()
        ->and($c->suggestion('a/texte-lite-1', 'vision'))->toBeNull();
});

it('remplace les modeles retires depuis la page', function () {
    catalogue();
    Repeater::fake();
    $this->actingAs(Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');

    Livewire::test(ModelesIA::class)
        ->set('data.text.1', [['id' => 'a/texte-lite-1'], ['id' => 'a/texte']])
        ->callAction('remplacerTout')
        ->assertSet('data.text.1.0.id', 'a/texte-lite-2');
});
