<?php

use App\Models\User;
use App\Services\Espace\PdfBook;

beforeEach(function () {
    $this->creatif = User::factory()->create(['firstname' => 'Marie', 'lastname' => 'Martin']);
});

it('genere le PDF du book en A4 portrait', function () {
    $pdf = app(PdfBook::class)->generer($this->creatif);

    expect($pdf)->toStartWith('%PDF')
        ->and($pdf)->toContain('/MediaBox [0 0 595.28 841.89]');
});

it('ne pose le code QR en couverture que sur demande', function () {
    $sans = app(PdfBook::class)->generer($this->creatif);
    $avec = app(PdfBook::class)->generer($this->creatif, qr: true);

    expect(substr_count($sans, '/Subtype /Image'))->toBe(0)
        ->and(substr_count($avec, '/Subtype /Image'))->toBe(1);
});

it('transmet l option qr au generateur depuis la page Exporter', function () {
    $this->actingAs($this->creatif)->get(route('espace.pdf', ['qr' => 1]))
        ->assertOk()->assertHeader('content-type', 'application/pdf');
});
