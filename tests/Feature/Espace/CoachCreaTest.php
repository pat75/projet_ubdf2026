<?php

use App\Http\Controllers\Admin\PriseIdentiteController;
use App\Livewire\Espace\Diffusion;
use App\Mail\CoachingMail;
use App\Models\CoachMessage;
use App\Models\CreatifActivity;
use App\Models\User;
use App\Services\Coach\Diagnostic;
use App\Services\Coach\Redacteur;
use Illuminate\Support\Facades\Http;
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
    expect(app(Diagnostic::class)->pour($this->creatif))->toContain('[Mon portfolio › Configurer] Personnaliser l’icône de profil du portfolio, la première image que les visiteurs associent au nom.');

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
        ->get('/admin_/coach-crea')->assertOk()->assertSee('Coach créatif')->assertSee('Ajouté')->assertSee('Galerie « Mode »');
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

it('liste les books actifs comme la liste des creatifs, operations repliees dessous', function () {
    CreatifActivity::create(['user_id' => $this->creatif->id, 'action' => 'created', 'subject_type' => 'App\Models\Gallery', 'subject_label' => 'Zzgal']);
    $inactif = User::factory()->create();
    $this->actingAs(\App\Models\Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');

    Livewire::test(\App\Filament\Pages\CoachCrea::class)
        ->assertCanSeeTableRecords([$this->creatif])
        ->assertCanNotSeeTableRecords([$inactif])
        ->assertSee('Zzgal');
});

it('redige avec l IA choisie : OpenRouter au niveau de cout regle, NVIDIA en panne bascule sur OpenRouter', function () {
    config(['services.nvidia.api_key' => 'cle-test', 'services.openrouter.api_key' => 'test-key']);
    $texte = ['choices' => [['message' => ['content' => "Objet : Votre book\nBonjour"]]]];
    Http::fake([
        'integrate.api.nvidia.com/*' => Http::response('saturé', 503),
        'openrouter.ai/*' => Http::response($texte),
    ]);

    // NVIDIA (defaut) en panne : OpenRouter prend le relais.
    $message = app(Redacteur::class)->rediger($this->creatif, collect(), ['x']);
    expect($message['objet'])->toBe('Votre book')
        ->and($message['modele'])->toBe('deepseek/deepseek-v4.1-flash');

    // OpenRouter niveau 3 choisi : NVIDIA n'est plus appele.
    \App\Models\Reglage::definirTexte(\App\Models\Reglage::COACH_IA, '3');
    Http::fake(['openrouter.ai/*' => Http::response($texte)]);
    expect(app(Redacteur::class)->rediger($this->creatif, collect(), ['x'])['modele'])->toBe('openai/gpt-4o');
});

it('affiche et enregistre l IA de redaction choisie sur la page Coach', function () {
    $this->actingAs(\App\Models\Admin::create(['name' => 'Pat', 'email' => 'admin@example.test', 'password' => 'mot-de-passe-long']), 'admin');
    CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'diagnostic' => ['x'], 'objet' => 'A', 'corps' => 'B', 'modele' => 'openai/gpt-4o']);

    $this->get('/admin_/coach-crea')->assertOk()->assertSee('IA de rédaction')->assertSee('openai/gpt-4o');

    Livewire::test(\App\Filament\Pages\CoachCrea::class)->set('coachIa', '2');
    expect(Redacteur::choix())->toBe('2');
});

it('fait du menu cite un lien dans le mail', function () {
    $message = new App\Models\CoachMessage(['corps' => "Bonjour Anne,\nPour le faire rapidement : « Mon portfolio › Configurer »"]);

    expect($message->corpsPourMail())->toContain('](' . lien('espace.design') . ')');
});

it('ouvre le brouillon dans une fenetre et le fait relire sans toucher au fond', function () {
    $admin = App\Models\Admin::create(['name' => 'Pat', 'email' => 'coach@example.test', 'password' => 'mot-de-passe-long']);
    $this->actingAs($admin, 'admin');
    Filament\Facades\Filament::setCurrentPanel('admin');

    $message = CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'objet' => 'Votre icone', 'corps' => 'Bonjour, vous pouriez', 'statut' => 'brouillon', 'diagnostic' => []]);

    $redacteur = Mockery::mock(Redacteur::class);
    $redacteur->shouldReceive('relire')->once()->with('Votre icone', 'Bonjour, vous pouriez')
        ->andReturn(['objet' => 'Votre icône', 'corps' => 'Bonjour, vous pourriez', 'modele' => 'test']);
    app()->instance(Redacteur::class, $redacteur);

    Livewire::test(App\Filament\Pages\CoachCrea::class)
        ->call('ouvrir', $message->id)
        ->assertSet('enEdition', $message->id)
        ->assertDispatched('open-modal', id: 'coach-brouillon')
        ->call('relire', $message->id)
        ->assertSet("brouillons.{$message->id}.corps", 'Bonjour, vous pourriez');
});

it('remplace le brouillon precedent du meme createur a la generation', function () {
    $admin = App\Models\Admin::create(['name' => 'Pat', 'email' => 'coach2@example.test', 'password' => 'mot-de-passe-long']);
    $this->actingAs($admin, 'admin');
    Filament\Facades\Filament::setCurrentPanel('admin');
    $ancien = CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'objet' => 'Ancien', 'corps' => 'Ancien', 'statut' => 'brouillon', 'diagnostic' => []]);

    $redacteur = Mockery::mock(Redacteur::class);
    $redacteur->shouldReceive('rediger')->andReturn(['objet' => 'Nouveau', 'corps' => 'Nouveau', 'modele' => 'test']);
    app()->instance(Redacteur::class, $redacteur);

    Livewire::test(App\Filament\Pages\CoachCrea::class)
        ->call('generer', $this->creatif->id)
        ->assertSet("brouillons.{$ancien->id}.objet", 'Nouveau');

    expect(CoachMessage::where('user_id', $this->creatif->id)->where('statut', 'brouillon')->pluck('objet')->all())->toBe(['Nouveau']);
});

it('supprime un brouillon', function () {
    $admin = App\Models\Admin::create(['name' => 'Pat', 'email' => 'coach3@example.test', 'password' => 'mot-de-passe-long']);
    $this->actingAs($admin, 'admin');
    Filament\Facades\Filament::setCurrentPanel('admin');
    $m = CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'objet' => 'A', 'corps' => 'A', 'statut' => 'brouillon', 'diagnostic' => []]);

    Livewire::test(App\Filament\Pages\CoachCrea::class)->call('supprimer', $m->id);

    expect(CoachMessage::count())->toBe(0);
});

it('valide le brouillon sans l envoyer', function () {
    Illuminate\Support\Facades\Mail::fake();
    $admin = App\Models\Admin::create(['name' => 'Pat', 'email' => 'coach4@example.test', 'password' => 'mot-de-passe-long']);
    $this->actingAs($admin, 'admin');
    Filament\Facades\Filament::setCurrentPanel('admin');
    $m = CoachMessage::create(['user_id' => $this->creatif->id, 'session_fin' => now(), 'objet' => 'A', 'corps' => 'A', 'statut' => 'brouillon', 'diagnostic' => []]);

    Livewire::test(App\Filament\Pages\CoachCrea::class)
        ->set("brouillons.{$m->id}.corps", 'Corrigé')
        ->call('valider', $m->id)
        ->assertDispatched('close-modal', id: 'coach-brouillon');

    expect($m->fresh())->statut->toBe('brouillon')->corps->toBe('Corrigé');
    Illuminate\Support\Facades\Mail::assertNothingSent();
});
