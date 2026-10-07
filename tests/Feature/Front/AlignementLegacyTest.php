<?php

use App\Models\User;
use App\Repository\BookRepository;

function bookDiffuse(array $attributs): User
{
    $user = User::factory()->create($attributs);
    $user->bookSetting()->create(['diffuse_web' => true, 'diffuse_ub' => true]);
    $user->media()->create(['filename' => 'v.jpg', 'status' => 'published']);

    return $user;
}

it("ordonne l'accueil comme le legacy : selection, puis nombre de visuels", function () {
    // Selectionnee depuis longtemps mais la plus fournie : elle reste en tete.
    $ancienne = bookDiffuse(['in_home_selection' => true, 'home_selection_at' => now()->subYears(3), 'media_count' => 400]);
    $recente = bookDiffuse(['in_home_selection' => true, 'home_selection_at' => now(), 'media_count' => 50]);
    $horsSelection = bookDiffuse(['in_home_selection' => false, 'media_count' => 900]);

    expect(app(BookRepository::class)->portfolios()->pluck('id')->all())
        ->toBe([$ancienne->id, $recente->id, $horsSelection->id]);
});

it('compte les books comme le legacy : comptes a mail confirme, sans condition de diffusion', function () {
    User::factory()->count(3)->create(['email_verified_at' => now()]);
    User::factory()->create(['email_verified_at' => null]);

    $this->get('https://'.config('ubdf.book_domain').'/cache_js/data_stats.json')
        ->assertOk()
        ->assertJsonPath('menu_stats.nb_book', (string) User::whereNotNull('email_verified_at')->count());
});
