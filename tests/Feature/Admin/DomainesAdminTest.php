<?php

use App\Mail\RelanceDomaine;
use App\Models\Admin;
use App\Models\Domaine;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'jeanne']);
});

function domaineRelance(User $u, int $jours, array $attrs = []): Domaine
{
    return Domaine::create($attrs + [
        'user_id' => $u->id, 'nom' => 'jeanne-dupont.fr', 'statut' => 'actif', 'prix_ttc' => 16,
        'titulaire' => ['email' => 'jeanne@exemple.fr'], 'reserve_le' => now()->subYear(), 'expire_le' => today()->addDays($jours),
    ]);
}

it('liste les noms de domaine commandes dans l administration', function () {
    domaineRelance($this->creatif, 40, ['resilie_le' => now()->subDay(), 'relances_envoyees' => 2]);
    $admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);

    $this->actingAs($admin, 'admin')->get('/admin_/domaines')
        ->assertOk()
        ->assertSee('jeanne-dupont.fr')
        ->assertSee('jeanne')
        ->assertSee('2 / 3');
});

it('relance a J-30, J-15 puis J-5, une seule fois par palier', function () {
    Mail::fake();
    $domaine = domaineRelance($this->creatif, 30);

    $this->artisan('ubdf:relancer-domaines');
    $this->artisan('ubdf:relancer-domaines');
    Mail::assertQueued(RelanceDomaine::class, 1);
    expect($domaine->fresh()->relances_envoyees)->toBe(1);

    $domaine->update(['expire_le' => today()->addDays(15)]);
    $this->artisan('ubdf:relancer-domaines');
    $domaine->update(['expire_le' => today()->addDays(5)]);
    $this->artisan('ubdf:relancer-domaines');

    Mail::assertQueued(RelanceDomaine::class, 3);
    Mail::assertQueued(RelanceDomaine::class, fn ($m) => $m->hasTo('jeanne@exemple.fr') && $m->joursRestants === 5);
    expect($domaine->fresh())->relances_envoyees->toBe(3)->derniere_relance_le->not->toBeNull();
});

it('n envoie qu une relance a un domaine decouvert tard', function () {
    Mail::fake();
    domaineRelance($this->creatif, 10);

    $this->artisan('ubdf:relancer-domaines');

    Mail::assertQueued(RelanceDomaine::class, 1);
    expect(Domaine::sole()->relances_envoyees)->toBe(2);
});

it('ne relance ni un domaine resilie ni un domaine lointain', function () {
    Mail::fake();
    domaineRelance($this->creatif, 10, ['statut' => 'resilie']);
    domaineRelance(User::factory()->create(), 60, ['nom' => 'loin.fr']);

    $this->artisan('ubdf:relancer-domaines');

    Mail::assertNothingSent();
});

it('rend le mail de relance', function () {
    $domaine = domaineRelance($this->creatif, 15);

    $html = (new RelanceDomaine($domaine, App\Support\Marque::depuisCode('ub'), 15))->render();

    expect($html)->toContain('jeanne-dupont.fr')->toContain('expire dans 15 jours')->toContain('/espace/domaine');
});

it('relance en anglais un createur Dustfolio', function () {
    Mail::fake();
    $this->creatif->update(['brand' => 'df']);
    domaineRelance($this->creatif, 30);

    $this->artisan('ubdf:relancer-domaines');

    Mail::assertQueued(RelanceDomaine::class, function (RelanceDomaine $m) {
        return $m->locale === 'en'
            && str_contains($m->render(), 'Manage my domain name')
            && str_contains($m->render(), 'portfolio');
    });
});
