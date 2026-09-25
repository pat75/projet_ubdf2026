<?php

use App\Livewire\Espace\Compte;
use App\Models\User;
use App\Services\Espace\ArchiveBook;
use App\Support\DossierBook;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

/*
 | L'ecran « Mon compte », repris de la capture de l'espace d'origine :
 | l'en-tete illustre, le rappel du mode de connexion, les sections
 | depliables et les champs de la fiche.
 */

it('montre l en-tete, le rappel de connexion et les sections', function () {
    $this->actingAs(User::factory()->create())->get(route('espace.compte'))
        ->assertOk()
        ->assertSeeInOrder([
            'Mes informations',
            'publiques et privées',
            'Votre connexion s’est faite via votre identifiant et votre mot de passe',
            'Votre compte',
            'Url de votre book',
            'Identifiant / Login',
            'Métier',
            'Votre statut',
            'Contacts par SMS',
            'Localisation',
        ], escape: false);
});

it('propose les statuts professionnels du legacy', function () {
    $creatif = User::factory()->create(['status' => 'Freelance']);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->assertSet('statut', 'Freelance')
        // Enregistre des que la liste change, sans bouton.
        ->set('statut', 'Maison des artistes');

    expect($creatif->fresh()->status)->toBe('Maison des artistes');
});

it('ignore un statut herite du legacy sous forme de nombre', function () {
    // La reprise a laisse des indices bruts dans quelques fiches : on ne
    // les affiche pas comme si c'etait un libelle.
    $creatif = User::factory()->create(['status' => '6']);

    Livewire::actingAs($creatif)->test(Compte::class)->assertSet('statut', '');
});

it('refuse un statut hors liste', function () {
    $creatif = User::factory()->create();

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('statut', 'Pirate')
        ->assertHasErrors('statut');

    expect($creatif->fresh()->status)->not->toBe('Pirate');
});

it('enregistre le consentement aux SMS', function () {
    $creatif = User::factory()->create(['accepts_sms' => false]);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->toggle('sms');

    expect($creatif->fresh()->accepts_sms)->toBeTrue();
});

it('supprime le portfolio quand le createur recopie son identifiant', function () {
    $creatif = User::factory()->create(['login' => 'aline', 'password' => 'mot-de-passe-long']);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('confirmationSuppression', 'aline')
        ->set('motDePasseActuel', 'mot-de-passe-long')
        ->call('supprimerPortfolio');

    // Suppression douce : la fiche quitte le site, la ligne reste.
    expect(User::where('login', 'aline')->exists())->toBeFalse()
        ->and(User::withTrashed()->where('login', 'aline')->exists())->toBeTrue()
        ->and(auth()->check())->toBeFalse();
});

it('ne supprime rien si l identifiant ou le mot de passe ne suit pas', function () {
    $creatif = User::factory()->create(['login' => 'aline', 'password' => 'mot-de-passe-long']);

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('confirmationSuppression', 'alin')
        ->set('motDePasseActuel', 'mot-de-passe-long')
        ->call('supprimerPortfolio')
        ->assertHasErrors('confirmationSuppression');

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('confirmationSuppression', 'aline')
        ->set('motDePasseActuel', 'au-hasard')
        ->call('supprimerPortfolio')
        ->assertHasErrors('motDePasseActuel');

    expect(User::where('login', 'aline')->exists())->toBeTrue();
});

it('archive les images du book supprime dans storage/books_effaces', function () {
    $login = 'zz'.uniqid();
    $creatif = User::factory()->create(['login' => $login, 'password' => 'mot-de-passe-long']);
    File::ensureDirectoryExists(DossierBook::chemin($login, 'galerie'));
    File::put(DossierBook::chemin($login, 'galerie/visuel.jpg'), 'jpg');
    File::put(DossierBook::chemin($login, 'notes.txt'), 'txt');

    Livewire::actingAs($creatif)->test(Compte::class)
        ->set('confirmationSuppression', $login)
        ->set('motDePasseActuel', 'mot-de-passe-long')
        ->call('supprimerPortfolio');

    $archives = File::glob(storage_path(ArchiveBook::DOSSIER.'/'.$login.'_*.zip'));
    $zip = new ZipArchive;
    $zip->open($archives[0]);

    expect($archives)->toHaveCount(1)
        ->and($zip->numFiles)->toBe(2)
        ->and($zip->locateName('galerie/visuel.jpg'))->not->toBeFalse()
        ->and(json_decode($zip->getFromName('creatif.json'), true)['creatif'])
            ->toHaveKey('login', $login)->not->toHaveKey('password')
        ->and(File::isDirectory(DossierBook::chemin($login)))->toBeFalse();

    $zip->close();
    File::delete($archives);
});
