<?php

use App\Http\Controllers\Admin\PriseIdentiteController;
use App\Livewire\Espace\Diffusion;
use App\Mail\CoachingMail;
use App\Models\CoachMessage;
use App\Models\CreatifActivity;
use App\Models\User;
use App\Services\Coach\Diagnostic;
use App\Services\Coach\Redacteur;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create();
});

it('journalise les modifications du book faites par le createur', function () {
    $this->actingAs($this->creatif);
    $this->creatif->galleries()->create(['name' => 'Mode', 'slug' => 'mode']);

    expect(CreatifActivity::sole())
        ->user_id->toBe($this->creatif->id)
        ->resume()->toBe('Ajouté Galerie « Mode »');
});

it('ne journalise rien pendant une prise d identite ni hors connexion', function () {
    $this->creatif->galleries()->create(['name' => 'Hors connexion', 'slug' => 'a']);

    $this->actingAs($this->creatif);
    session()->put(PriseIdentiteController::SESSION, 1);
    $this->creatif->galleries()->create(['name' => 'Admin', 'slug' => 'b']);

    expect(CreatifActivity::count())->toBe(0);
});

it('active les conseils par defaut et se coupe depuis Diffusion', function () {
    $this->actingAs($this->creatif);

    Livewire::test(Diffusion::class)->assertSet('coaching', true)->call('basculer', 'coaching')->assertSet('coaching', false);

    expect($this->creatif->fresh()->bookSetting->coaching)->toBeFalse();
});

it('desabonne depuis le lien signe du mail', function () {
    $this->get(URL::signedRoute('coach.desabonnement', ['user' => $this->creatif]))->assertOk();

    expect($this->creatif->bookSetting->coaching)->toBeFalse();
});

it('conseille ce qui manque, puis la selection une fois le book complet', function () {
    expect(app(Diagnostic::class)->pour($this->creatif))->toContain('[Mon portfolio › Configurer] Ajouter un visuel de profil.');

    $this->creatif->bookSetting()->create(['thumbnail' => 'a.jpg', 'title' => 'T', 'description' => 'Bio']);
    $galerie = $this->creatif->galleries()->create(['name' => 'G', 'slug' => 'g']);
    foreach (range(1, Diagnostic::VISUELS_MINIMUM) as $i) {
        $this->creatif->media()->create(['gallery_id' => $galerie->id, 'filename' => "$i.jpg"]);
    }

    expect(app(Diagnostic::class)->pour($this->creatif->fresh()))->toHaveCount(2)
        ->and(app(Diagnostic::class)->pour($this->creatif->fresh())[0])->toContain('sélection');
});

it('prepare un brouillon 24 h apres la derniere session, une fois par semaine', function () {
    $this->mock(Redacteur::class)->shouldReceive('rediger')->once()
        ->andReturn(['objet' => 'Votre book', 'corps' => 'Bonjour', 'modele' => 'test']);

    CreatifActivity::forceCreate(['user_id' => $this->creatif->id, 'action' => 'created', 'subject_type' => 'App\Models\Gallery', 'created_at' => now()->subHours(25)]);

    $this->artisan('ubdf:preparer-coaching')->assertSuccessful();
    $this->artisan('ubdf:preparer-coaching')->assertSuccessful();

    expect(CoachMessage::sole())->statut->toBe('brouillon')->objet->toBe('Votre book');
});

it('n ecrit pas a un createur qui a coupe les conseils', function () {
    $this->mock(Redacteur::class)->shouldNotReceive('rediger');
    $this->creatif->bookSetting()->create(['coaching' => false]);
    CreatifActivity::forceCreate(['user_id' => $this->creatif->id, 'action' => 'created', 'subject_type' => 'App\Models\Gallery', 'created_at' => now()->subHours(25)]);

    $this->artisan('ubdf:preparer-coaching')->assertSuccessful();

    expect(CoachMessage::count())->toBe(0);
});

it('envoie le message corrige par l administrateur, avec le lien de desabonnement', function () {
    Mail::fake();
    $message = CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'diagnostic' => ['x'], 'objet' => 'A', 'corps' => 'B']);

    $this->actingAs(\App\Models\Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');
    Livewire::test(\App\Filament\Pages\CoachCrea::class)
        ->assertSee('dernier message')
        ->set("brouillons.{$message->id}.corps", 'Corps corrigé')
        ->call('envoyer', $message->id);

    expect($message->fresh())->statut->toBe('envoye')->corps->toBe('Corps corrigé');
    Mail::assertQueued(CoachingMail::class, fn ($m) => str_contains($m->render(), 'coach/desabonnement'));
});

it('ouvre la page Coach crea avec l activite groupee par createur', function () {
    $this->actingAs($this->creatif);
    $this->creatif->galleries()->create(['name' => 'Mode', 'slug' => 'mode']);
    auth()->guard('web')->logout();

    $this->actingAs(\App\Models\Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin')
        ->get('/admin_/coach-crea')->assertOk()->assertSee('Coach créa')->assertSee('Ajouté Galerie « Mode »');
});

it('affiche les conseils dans un bloc Coach, hors du compteur de diffusion', function () {
    $this->actingAs($this->creatif)->get(route('espace.diffusion'))->assertOk()
        ->assertSeeInOrder(['Diffusion', 'Coach', 'Conseils pour mon book', 'Sélection'])
        ->assertSee('/ 4 canaux actifs', false);
});

it('liste les conseils envoyes dans le bloc Coach, sans les brouillons', function () {
    CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'diagnostic' => [], 'objet' => 'Conseil envoyé', 'corps' => 'Texte du conseil', 'statut' => 'envoye', 'envoye_le' => now()]);
    CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'diagnostic' => [], 'objet' => 'Brouillon caché', 'corps' => 'x']);

    $this->actingAs($this->creatif)->get(route('espace.diffusion'))->assertOk()
        ->assertSeeInOrder(['Derniers conseils reçus', 'Conseil envoyé', 'Texte du'])
        ->assertDontSee('Brouillon caché');
});

it('transforme les menus cites en liens vers leur page', function () {
    $m = new CoachMessage(['corps' => "Rendez-vous dans « Mon portfolio › Configurer ».\n<b>x</b>"]);

    expect((string) $m->corpsAvecLiens())
        ->toContain('href="'.lien('espace.design').'"')
        ->toContain('&lt;b&gt;');
});

it('soigne la typographie : insecables, pas de mot seul, une phrase par ligne dans le mail', function () {
    $m = new CoachMessage(['corps' => "Bonjour Adolie,\nUn détail : votre visuel.\n- Allez dans « Mon portfolio › Configurer »."]);

    expect(CoachMessage::typographie('Un détail : votre visuel ?'))->toBe("Un détail\u{202F}: votre\u{00A0}visuel\u{202F}?")
        ->and($m->corpsPourMail())->toContain("Adolie,  \nUn")
        ->and((string) $m->corpsAvecLiens())->toContain('href="'.lien('espace.design').'"');
});
