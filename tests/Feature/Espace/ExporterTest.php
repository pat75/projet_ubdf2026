<?php

use App\Models\User;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'microtest', 'firstname' => 'Léa', 'lastname' => 'Roux']);
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom', 'diffuse_web' => true]);
    $g = $this->creatif->galleries()->create(['name' => 'G', 'slug' => 'g', 'status' => 'published', 'position' => 1]);
    foreach (range(1, 12) as $i) {
        $g->media()->create(['user_id' => $this->creatif->id, 'filename' => "v{$i}.jpg", 'status' => 'published']);
    }
});

it('sert le microbook a l url du legacy, integrable par iframe', function () {
    $r = $this->get('/microbook_0_1__microtest')->assertOk()->assertSee('Léa Roux');

    expect(substr_count($r->getContent(), 'data-grande='))->toBe(10)
        ->and($r->headers->get('Content-Security-Policy'))->toBe('frame-ancestors *');
});

it('ne sert pas le microbook d un book hors ligne', function () {
    $this->creatif->bookSetting->update(['diffuse_web' => false]);

    $this->get('/microbook_0_1__microtest')->assertNotFound();
});

it('donne le code d integration dans l espace', function () {
    $this->actingAs($this->creatif)->get(route('espace.exporter'))->assertOk()
        ->assertSee('microbook_0_1__microtest', false);
});

it('produit le PDF du book, limite a 4 visuels en formule gratuite', function () {
    $dossier = storage_path('app/public/books/microtest');
    Illuminate\Support\Facades\File::ensureDirectoryExists($dossier);
    foreach (range(1, 12) as $i) {
        imagepng(imagecreatetruecolor(40, 30), $dossier."/v{$i}.jpg");
    }

    $r = $this->actingAs($this->creatif)->get(route('espace.pdf'))->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    // Couverture + 4 visuels.
    expect(preg_match_all('#/Type /Page\b#', $r->getContent()))->toBe(5);

    Illuminate\Support\Facades\File::deleteDirectory($dossier);
});

it('refuse le PDF a un visiteur', function () {
    $this->get(route('espace.pdf'))->assertRedirect();
});
