<?php

use App\Jobs\GenererExportCompte;
use App\Livewire\Espace\Exporter;
use App\Models\DataExport;
use App\Models\User;
use App\Services\Espace\MiseEnPagePdf;
use App\Support\DossierBook;
use App\Mail\ArchivePrete;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->creatif = User::factory()->create(['login' => 'microtest', 'firstname' => 'Léa', 'lastname' => 'Roux']);
    $this->creatif->bookSetting()->create(['theme' => 'mdl_2016_zoom', 'diffuse_web' => true]);
    $this->galerie = $this->creatif->galleries()->create(['name' => 'Affiches', 'slug' => 'affiches', 'status' => 'published', 'position' => 1]);
    foreach (range(1, 12) as $i) {
        $this->galerie->media()->create(['user_id' => $this->creatif->id, 'filename' => "v{$i}.jpg", 'status' => 'published']);
    }
    $this->dossier = DossierBook::chemin('microtest');
});

afterEach(fn () => File::deleteDirectory($this->dossier));

/** Visuels de test sur le disque, au format largeur x hauteur. */
function visuels(string $dossier, int $nombre, int $l = 40, int $h = 30): void
{
    File::ensureDirectoryExists($dossier);
    foreach (range(1, $nombre) as $i) {
        imagejpeg(imagecreatetruecolor($l, $h), $dossier."/v{$i}.jpg");
    }
}

it('sert le microbook a l url du legacy, integrable par iframe', function () {
    $r = $this->get('/microbook_0_1__microtest')->assertOk()->assertSee('Léa Roux');

    expect(substr_count($r->getContent(), 'data-grande='))->toBe(10)
        ->and($r->headers->get('Content-Security-Policy'))->toBe('frame-ancestors *');
});

it('ne sert pas le microbook d un book hors ligne', function () {
    $this->creatif->bookSetting->update(['diffuse_web' => false]);

    $this->get('/microbook_0_1__microtest')->assertNotFound();
});

it('n affiche plus le code d integration mais le PDF, l archive et les limites', function () {
    $this->actingAs($this->creatif)->get(route('espace.exporter'))->assertOk()
        ->assertDontSee('microbook_0_1__microtest', false)
        ->assertDontSee('<iframe', false)
        ->assertSee('Exporter mon book en PDF')
        ->assertSee('Préparer mon archive')
        ->assertSee('Limites selon la formule');
});

it('repartit les visuels selon leur format, sans changer leur ordre', function () {
    $ratios = ['p1' => 1.5, 'p2' => 1.5, 'p3' => 1.0, 'v1' => 0.7, 'v2' => 0.7, 'c1' => 1, 'c2' => 1, 'c3' => 1, 'c4' => 1, 'seul' => 0.7];
    $pages = MiseEnPagePdf::planifier(array_keys($ratios), fn ($v) => $ratios[$v]);

    expect(array_column($pages, 'gabarit'))->toBe([
        MiseEnPagePdf::GRANDE_DEUX, MiseEnPagePdf::QUATRE, MiseEnPagePdf::COTE_A_COTE, MiseEnPagePdf::PLEINE_PAGE,
    ])->and(array_merge(...array_column($pages, 'visuels')))->toBe(array_keys($ratios));

    expect(array_column(MiseEnPagePdf::planifier(['a', 'b'], fn () => 1.6), 'gabarit'))->toBe([MiseEnPagePdf::SUPERPOSEES]);
});

it('varie les gabarits et evite les visuels seuls', function () {
    // Huit carres : pas deux grilles de quatre d'affilee.
    expect(array_column(MiseEnPagePdf::planifier(range(1, 8), fn () => 1.0), 'gabarit'))
        ->toBe([MiseEnPagePdf::QUATRE, MiseEnPagePdf::COTE_A_COTE, MiseEnPagePdf::COTE_A_COTE]);

    // Un paysage et un portrait : une page a deux plutot que deux pleines pages.
    $ratios = [1.6, 0.7];
    expect(array_column(MiseEnPagePdf::planifier([0, 1], fn ($i) => $ratios[$i]), 'gabarit'))
        ->toBe([MiseEnPagePdf::COTE_A_COTE]);
});

it('produit le PDF du book, limite a 6 pages de visuels en formule gratuite', function () {
    // 12 visuels portrait : 6 pages de deux, toutes gardees.
    visuels($this->dossier, 12, 30, 45);
    foreach (range(13, 20) as $i) {
        $this->galerie->media()->create(['user_id' => $this->creatif->id, 'filename' => "v{$i}.jpg", 'status' => 'published']);
        imagejpeg(imagecreatetruecolor(30, 45), $this->dossier."/v{$i}.jpg");
    }

    $r = $this->actingAs($this->creatif)->get(route('espace.pdf'))->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');

    // Couverture + 6 pages, alors que 20 portraits en feraient 10.
    expect(preg_match_all('#/Type /Page\b#', $r->getContent()))->toBe(7);
});

it('refuse le PDF a un visiteur', function () {
    $this->get(route('espace.pdf'))->assertRedirect();
});

it('met la demande d archive en file et en limite le nombre', function () {
    Queue::fake();

    Livewire::actingAs($this->creatif)->test(Exporter::class)
        ->call('demanderArchive')
        ->call('demanderArchive')
        ->assertSet('refus', fn ($refus) => str_contains($refus, 'nouvelle archive'));

    Queue::assertPushed(GenererExportCompte::class, 1);
    expect(DataExport::where('user_id', $this->creatif->id)->count())->toBe(1);
});

it('annonce l e-mail pendant la preparation puis le lien une fois l archive prete', function () {
    Storage::fake(DataExport::DISQUE);
    $export = new DataExport;
    $export->user()->associate($this->creatif)->save();

    Livewire::actingAs($this->creatif)->test(Exporter::class)
        ->assertSee('Vous recevrez un e-mail à '.$this->creatif->email, false)
        ->assertDontSee('Préparer mon archive');

    Storage::disk(DataExport::DISQUE)->put('exports/a.zip', 'zip');
    $export->update(['status' => DataExport::PRET, 'fichier' => 'exports/a.zip', 'taille' => 3, 'termine_at' => now(), 'expire_at' => now()->addDays(7)]);

    Livewire::actingAs($this->creatif)->test(Exporter::class)
        ->assertSee('Archive prête')
        ->assertSee(route('espace.export.telecharger', $export), false);
});

it('laisse redemander une archive apres un echec', function () {
    Queue::fake();
    $echec = new DataExport(['status' => DataExport::ECHEC]);
    $echec->user()->associate($this->creatif)->save();

    Livewire::actingAs($this->creatif)->test(Exporter::class)->call('demanderArchive')->assertSet('refus', null);

    Queue::assertPushed(GenererExportCompte::class, 1);
});

it('construit l archive des donnees, images HD comprises, sans secret', function () {
    Storage::fake(DataExport::DISQUE);
    Mail::fake();
    visuels($this->dossier, 12);
    $this->creatif->conversations()->create(['subject' => 'Affiche', 'sender_name' => 'Client', 'sender_email' => 'c@example.test'])
        ->messages()->create(['from_owner' => false, 'body' => 'Bonjour']);

    $export = new DataExport;
    $export->user()->associate($this->creatif)->save();
    (new GenererExportCompte($export))->handle(app(App\Services\Espace\ExportCompte::class));

    $export->refresh();
    expect($export->status)->toBe(DataExport::PRET)->and($export->telechargeable())->toBeTrue();

    $zip = new ZipArchive;
    $zip->open(Storage::disk(DataExport::DISQUE)->path($export->fichier));
    $compte = json_decode($zip->getFromName('compte.json'), true);

    expect($zip->locateName('index.html'))->not->toBeFalse()
        ->and($zip->locateName('images/01-affiches/001-v1.jpg'))->not->toBeFalse()
        ->and($compte['compte']['login'])->toBe('microtest')
        ->and($compte['compte'])->not->toHaveKey('password')
        ->and(json_decode($zip->getFromName('messages.json'), true)[0]['messages'][0]['texte'])->toBe('Bonjour')
        ->and(count(json_decode($zip->getFromName('portfolios.json'), true)[0]['visuels']))->toBe(12);
    $zip->close();

    Mail::assertSent(ArchivePrete::class, fn ($mail) => $mail->hasTo($this->creatif->email)
        && str_contains($mail->lien, '/espace/exporter'));

    $this->actingAs($this->creatif)->get(route('espace.export.telecharger', $export))->assertOk()
        ->assertDownload();
    $this->actingAs(User::factory()->create())->get(route('espace.export.telecharger', $export))->assertForbidden();
});

it('masque le nom des rubriques du PDF sur demande', function () {
    visuels($this->dossier, 2);
    $this->galerie->update(['name' => 'Rubriquetest']);

    $avec = $this->actingAs($this->creatif)->get(route('espace.pdf'))->getContent();
    $sans = $this->actingAs($this->creatif)->get(route('espace.pdf', ['titres' => 0]))->getContent();

    // FPDF compresse les flux, le texte n y est pas lisible : le titre
    // en tete des pages alourdit seulement le fichier.
    expect(strlen($avec))->toBeGreaterThan(strlen($sans));
});

it('ajoute le titre des visuels sous les images sur demande', function () {
    visuels($this->dossier, 2);
    $this->galerie->media()->update(['title' => 'Un titre de visuel assez long pour peser']);

    $sans = $this->actingAs($this->creatif)->get(route('espace.pdf'))->getContent();
    $avec = $this->actingAs($this->creatif)->get(route('espace.pdf', ['legendes' => 1]))->getContent();

    expect(strlen($avec))->toBeGreaterThan(strlen($sans));
});

it('ne reprend pas un titre de visuel qui n est que le nom du fichier', function () {
    visuels($this->dossier, 2);

    $this->galerie->media()->update(['title' => '']);
    $vide = strlen($this->actingAs($this->creatif)->get(route('espace.pdf', ['legendes' => 1]))->getContent());

    $this->galerie->media()->update(['title' => 'Affiche finale version longue.JPG']);
    $fichier = strlen($this->actingAs($this->creatif)->get(route('espace.pdf', ['legendes' => 1]))->getContent());

    // Au jour pres du pied de page, le PDF est le meme que sans titre.
    expect(abs($fichier - $vide))->toBeLessThan(20);
});
