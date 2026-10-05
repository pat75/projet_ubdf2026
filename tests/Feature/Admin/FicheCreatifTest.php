<?php

use App\Http\Controllers\Admin\PriseIdentiteController;
use App\Mail\NouveauMotDePasse;
use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\User;
use App\Services\Admin\MotDePasseTemporaire;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']);
    $this->creatif = User::factory()->create(['login' => 'ariane', 'brand' => 'ub']);
});

/*
 | Prise d'identite
 */

it('ouvre l espace du creatif sous son identite', function () {
    $this->actingAs($this->admin, 'admin')
        ->post('/admin_/prise-identite/'.$this->creatif->login)
        ->assertRedirect(route('espace'));

    expect(Auth::guard('web')->id())->toBe($this->creatif->id)
        // La session administrateur reste ouverte : c'est elle qui permet
        // de revenir sans ressaisir un mot de passe.
        ->and(Auth::guard('admin')->id())->toBe($this->admin->id)
        ->and(session(PriseIdentiteController::SESSION))->toBe($this->admin->id);
});

it('affiche un bandeau de retour dans l espace emprunte', function () {
    $this->actingAs($this->admin, 'admin')->post('/admin_/prise-identite/'.$this->creatif->login);

    // `actingAs` a fait de `admin` la garde par defaut du test ; dans un
    // vrai navigateur, l'espace repond sous la garde `web`.
    Auth::shouldUse('web');

    $this->get('/espace')->assertOk()
        ->assertSee('connecté en tant que ariane', escape: false)
        ->assertSee('Revenir au back-office');
});

it('rend la main et revient au back-office', function () {
    $this->actingAs($this->admin, 'admin')->post('/admin_/prise-identite/'.$this->creatif->login);

    $this->post('/admin_/prise-identite')->assertRedirect('/admin_/users');

    expect(Auth::guard('web')->check())->toBeFalse()
        ->and(session()->has(PriseIdentiteController::SESSION))->toBeFalse();
});

it('refuse la prise d identite sans session administrateur', function () {
    $this->post('/admin_/prise-identite/'.$this->creatif->login)->assertRedirect();

    expect(Auth::guard('web')->check())->toBeFalse();
});

it('journalise la prise d identite', function () {
    $this->actingAs($this->admin, 'admin')->post('/admin_/prise-identite/'.$this->creatif->login);

    $trace = AdminActivity::where('action', 'prise_identite')->sole();

    expect($trace->subject_label)->toBe('ariane')
        ->and($trace->admin_id)->toBe($this->admin->id);
});

/*
 | Mot de passe
 */

it('tire un mot de passe de huit caracteres alphanumeriques', function () {
    $service = app(MotDePasseTemporaire::class);

    foreach (range(1, 50) as $ignore) {
        expect($service->generer())->toMatch('/^[a-zA-Z0-9]{8}$/');
    }
});

it('applique le nouveau mot de passe et oublie l ancien', function () {
    $ancien = $this->creatif->password;

    $clair = app(MotDePasseTemporaire::class)->appliquer($this->creatif);

    $this->creatif->refresh();

    expect(Hash::check($clair, $this->creatif->password))->toBeTrue()
        ->and($this->creatif->password)->not->toBe($ancien)
        // Rien n'est conserve en clair : seule la reponse le porte.
        ->and($this->creatif->getAttributes())->not->toContain($clair);
});

it('reinitialise puis envoie le mot de passe depuis la fiche', function () {
    Mail::fake();

    $this->actingAs($this->admin, 'admin');

    $page = Livewire::test(App\Filament\Resources\Users\Pages\EditUser::class, ['record' => $this->creatif->login]);

    // Tant que rien n'a ete tire, le bouton d'envoi n'existe pas.
    $page->assertActionHidden('envoyer_mot_de_passe')
        ->callAction('reinitialiser_mot_de_passe')
        ->assertActionVisible('envoyer_mot_de_passe');

    $clair = $page->get('motDePasseGenere');

    expect($clair)->toMatch('/^[a-zA-Z0-9]{8}$/')
        ->and(Hash::check($clair, $this->creatif->fresh()->password))->toBeTrue();

    $page->callAction('envoyer_mot_de_passe');

    Mail::assertQueued(NouveauMotDePasse::class, fn (NouveauMotDePasse $mail) => $mail->hasTo($this->creatif->email)
        && $mail->motDePasse === $clair);

    // Une fois parti, il disparait de l'ecran.
    $page->assertActionHidden('envoyer_mot_de_passe');
});

/*
 | Selection en page d'accueil
 */

it('date la selection au moment ou on la coche', function () {
    $this->creatif->update(['in_home_selection' => true]);

    expect($this->creatif->fresh()->home_selection_at)->not->toBeNull();
});

it('efface la date quand on retire le book de la selection', function () {
    $this->creatif->update(['in_home_selection' => true]);
    $this->creatif->update(['in_home_selection' => false]);

    expect($this->creatif->fresh()->home_selection_at)->toBeNull();
});

it('respecte une date de selection saisie a la main', function () {
    $veille = now()->subMonth()->startOfDay();

    $this->creatif->update(['in_home_selection' => true, 'home_selection_at' => $veille]);

    expect($this->creatif->fresh()->home_selection_at->toDateString())->toBe($veille->toDateString());
});

it('montre le calendrier de selection sur la fiche', function () {
    // Le calendrier n'apparait que si le book est en selection : il n'a
    // rien a dire tant que l'interrupteur est ferme.
    $this->creatif->update(['in_home_selection' => true]);

    $this->actingAs($this->admin, 'admin')->get('/admin_/users/'.$this->creatif->login.'/edit')
        ->assertOk()
        ->assertSee('Date de la sélection')
        ->assertSee('Se connecter en tant que')
        ->assertSee('Réinitialiser le mot de passe');
});

it('affiche identifiant et nom sur une seule ligne dans la liste des createurs', function () {
    $this->actingAs(App\Models\Admin::create(['name' => 'Pat', 'email' => 'admin2@example.test', 'password' => 'mot-de-passe-long']), 'admin');
    App\Models\User::factory()->create(['login' => 'ariane9', 'firstname' => 'Ariane', 'lastname' => 'Martin']);

    Livewire::test(App\Filament\Resources\Users\Pages\ListUsers::class)
        ->assertSeeHtml('<span class="ub-creatif-login">ariane9</span><span class="ub-creatif-nom">Ariane Martin</span>');
});
