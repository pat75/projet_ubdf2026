<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->book = User::factory()->create(['login' => 'statbook']);
    $this->url = 'https://statbook.'.config('ubdf.book_domain').'/ubstats.gif';
});

it('compte une visite une fois par visiteur et par demi-heure', function () {
    $this->withHeader('User-Agent', 'Mozilla/5.0')->get($this->url)->assertOk()->assertHeader('Content-Type', 'image/gif');
    $this->withHeader('User-Agent', 'Mozilla/5.0')->get($this->url);

    expect($this->book->visitStats()->sole()->public_views)->toBe(1);

    Cache::flush();
    $this->withHeader('User-Agent', 'Mozilla/5.0')->get($this->url);
    expect($this->book->visitStats()->sole()->public_views)->toBe(2);
});

it('ne compte ni les robots ni le proprietaire comme visiteur', function () {
    $this->withHeader('User-Agent', 'Googlebot/2.1')->get($this->url);
    $this->actingAs($this->book)->withHeader('User-Agent', 'Mozilla/5.0')->get($this->url);

    expect($this->book->visitStats()->sole())->public_views->toBe(0)->admin_views->toBe(1);
});

it('affiche les statistiques dans l espace', function () {
    $this->book->visitStats()->create(['date' => now()->toDateString(), 'public_views' => 42]);
    $this->book->visitStats()->create(['date' => '2012-02-01', 'public_views' => 5341]);

    $this->actingAs($this->book)->get(route('espace.statistiques'))->assertOk()
        ->assertSee('42')->assertSee('5 383');
});

it('n appelle plus le serveur de statistiques du legacy', function () {
    expect(collect(File::allFiles(resource_path('views/book')))
        ->filter(fn ($f) => str_contains($f->getContents(), 'extra-book.com'))->count())->toBe(0);
});

it('cumule les surfaces d un meme jour dans la courbe', function () {
    $this->book->visitStats()->create(['date' => now()->toDateString(), 'surface' => 'book', 'public_views' => 10]);
    $this->book->visitStats()->create(['date' => now()->toDateString(), 'surface' => 'minibook', 'public_views' => 3]);

    $parJour = $this->actingAs($this->book)->get(route('espace.statistiques'))->assertOk()->viewData('parJour');

    expect($parJour->last())->toMatchArray(['book' => 10, 'minibook' => 3])
        ->and($parJour)->toHaveCount(90);
});
