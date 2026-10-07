<?php

use App\Models\User;
use App\Repository\BookRepository;

function bookAccueilLegacy(array $attributs): User
{
    $user = User::factory()->create($attributs);
    $user->bookSetting()->create(['diffuse_web' => true, 'diffuse_ub' => true]);
    $user->media()->create(['filename' => 'v.jpg', 'status' => 'published']);

    return $user;
}

it("ordonne l'accueil comme le legacy : derniere date de selection d'abord", function () {
    $ancienne = bookAccueilLegacy(['in_home_selection' => true, 'home_selection_at' => now()->subYears(3), 'media_count' => 400]);
    $recente = bookAccueilLegacy(['in_home_selection' => true, 'home_selection_at' => now()->subDay(), 'media_count' => 50]);
    $horsSelection = bookAccueilLegacy(['in_home_selection' => false, 'media_count' => 900]);

    expect(app(BookRepository::class)->portfolios()->pluck('id')->all())
        ->toBe([$recente->id, $ancienne->id, $horsSelection->id]);
});

it('reprend les dates de selection de inc_stats, sans les redater', function () {
    // La connexion legacy est en lecture seule : on la simule par une table en memoire.
    config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('legacy');
    Schema::connection('legacy')->create('inc_stats', function ($t) {
        $t->integer('st_id_user');
        $t->string('st_selection_date');
    });
    DB::connection('legacy')->table('inc_stats')->insert([
        ['st_id_user' => 10, 'st_selection_date' => '2016-10-10'],
        ['st_id_user' => 10, 'st_selection_date' => '2024-03-01'],
        ['st_id_user' => 11, 'st_selection_date' => '0000-00-00'],
    ]);
    $avecDate = bookAccueilLegacy(['legacy_id' => 10, 'in_home_selection' => true]);
    $sansDate = bookAccueilLegacy(['legacy_id' => 11, 'in_home_selection' => true]);

    $this->artisan('ubdf:legacy:accueil --dates')->assertSuccessful();

    expect($avecDate->fresh()->home_selection_at->toDateString())->toBe('2024-03-01')
        ->and($sansDate->fresh()->home_selection_at)->toBeNull();
});

it('affiche les vues et les coeurs du legacy sur la carte, pas de coeur sous 2', function () {
    $illustrateur = App\Models\Category::create(['slug' => 'illustrateur', 'name' => 'Illustrateur']);
    $book = bookAccueilLegacy(['in_home_selection' => true, 'category_id' => $illustrateur->id]);
    User::whereKey($book->id)->update(['legacy_views' => 183157, 'legacy_likes' => 18]);
    $seul = bookAccueilLegacy(['in_home_selection' => true, 'category_id' => $illustrateur->id]);
    User::whereKey($seul->id)->update(['legacy_views' => 3137, 'legacy_likes' => 1]);

    $html = $this->get('https://'.config('ubdf.book_domain').'/')->assertOk()->getContent();

    expect($html)->toContain('<span class="stats_vue">183157</span>')
        ->toContain('<span class="stats_sel">18</span>')
        ->toContain('<span class="stats_vue">3137</span>')
        ->and(substr_count($html, 'class="stats_sel"'))->toBe(1);
});

it('compte les books comme le legacy : comptes a mail confirme, sans condition de diffusion', function () {
    User::factory()->count(3)->create(['email_verified_at' => now()]);
    User::factory()->create(['email_verified_at' => null]);

    $this->get('https://'.config('ubdf.book_domain').'/cache_js/data_stats.json')
        ->assertOk()
        ->assertJsonPath('menu_stats.nb_book', (string) User::whereNotNull('email_verified_at')->count());
});

it('reprend vues et coeurs depuis le serveur de stats du legacy', function () {
    config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('legacy');
    Schema::connection('legacy')->create('inc_user', function ($t) {
        $t->integer('us_id');
        $t->string('us_login');
    });
    DB::connection('legacy')->table('inc_user')->insert([
        ['us_id' => 20, 'us_login' => 'mapauvreamie'],
        ['us_id' => 21, 'us_login' => 'inconnu'],
        ['us_id' => 99, 'us_login' => 'non-repris'],
    ]);
    $matild = bookAccueilLegacy(['legacy_id' => 20]);
    $inconnu = bookAccueilLegacy(['legacy_id' => 21]);
    Http::fake(['extra-book.com/*' => Http::response('cb([{"st_book":183159,"st_memo":18},false]);')]);

    $this->artisan('ubdf:legacy:accueil --stats')->assertSuccessful();

    expect($matild->fresh())->legacy_views->toBe(183159)->legacy_likes->toBe(18)
        ->and($inconnu->fresh()->legacy_views)->toBe(0);
});

it('reprend login par login un paquet refuse par le serveur de stats', function () {
    config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('legacy');
    Schema::connection('legacy')->create('inc_user', function ($t) {
        $t->integer('us_id');
        $t->string('us_login');
    });
    DB::connection('legacy')->table('inc_user')->insert([['us_id' => 30, 'us_login' => 'bon'], ['us_id' => 31, 'us_login' => 'casse']]);
    $bon = bookAccueilLegacy(['legacy_id' => 30]);
    bookAccueilLegacy(['legacy_id' => 31]);
    Http::fake(function ($requete) {
        $logins = json_decode($requete['us_login'], true);

        return match ($logins) {
            ['bon'] => Http::response('cb([{"st_book":42,"st_memo":3}]);'),
            default => Http::response('', 500),
        };
    });

    $this->artisan('ubdf:legacy:accueil --stats')->assertSuccessful();

    expect($bon->fresh())->legacy_views->toBe(42)->legacy_likes->toBe(3);
});

it("garde les blocs de l'accueil en cache et les vide quand une selection change", function () {
    $illustrateur = App\Models\Category::create(['slug' => 'illustrateur', 'name' => 'Illustrateur']);
    $book = bookAccueilLegacy(['in_home_selection' => true, 'category_id' => $illustrateur->id, 'login' => 'premierbook']);
    $accueil = 'https://'.config('ubdf.book_domain').'/';

    $this->get($accueil)->assertOk()->assertSee('user_premierbook', false);

    // Ecriture sans evenement : le cache sert encore l'ancienne version.
    User::whereKey($book->id)->update(['login' => 'renomme']);
    $this->get($accueil)->assertSee('user_premierbook', false);

    // Une selection qui change vide le cache.
    $autre = bookAccueilLegacy(['category_id' => $illustrateur->id, 'login' => 'nouveau']);
    $autre->update(['in_home_selection' => true]);
    $this->get($accueil)->assertSee('user_nouveau', false)->assertSee('user_renomme', false);
});
