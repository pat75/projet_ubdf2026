<?php

use App\Models\NewsletterMail;
use App\Models\User;
use App\Services\Legacy\LegacyMigrator;
use App\Services\Legacy\LegacySampler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/*
| Base legacy de substitution : un fichier SQLite rempli par une connexion
| a part, puis lu par `legacy` comme le ferait la reprise.
*/
beforeEach(function () {
    $this->fichier = tempnam(sys_get_temp_dir(), 'legacy').'.sqlite';
    touch($this->fichier);

    config(['database.connections.legacy_essai' => ['driver' => 'sqlite', 'database' => $this->fichier]]);
    DB::connection('legacy_essai')->statement('CREATE TABLE nl_newsletter (nl_id INTEGER, nl_mail TEXT, nl_date_insc TEXT, nl_ip TEXT, nl_envoi_etat TEXT)');
    DB::connection('legacy_essai')->table('nl_newsletter')->insert([
        ['nl_id' => 1, 'nl_mail' => ' Ariane@Example.com ', 'nl_date_insc' => '2014-03-02', 'nl_ip' => '1.2.3.4', 'nl_envoi_etat' => 'on'],
        ['nl_id' => 2, 'nl_mail' => 'ariane@example.com', 'nl_date_insc' => '2015-01-01', 'nl_ip' => null, 'nl_envoi_etat' => 'on'],
        ['nl_id' => 3, 'nl_mail' => 'pas-une-adresse', 'nl_date_insc' => '2015-01-01', 'nl_ip' => null, 'nl_envoi_etat' => 'on'],
        ['nl_id' => 4, 'nl_mail' => 'parti@example.com', 'nl_date_insc' => '2016-06-01', 'nl_ip' => null, 'nl_envoi_etat' => 'off'],
    ]);

    config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => $this->fichier]]);
    DB::purge('legacy');
});

afterEach(function () {
    DB::purge('legacy');
    DB::purge('legacy_essai');
    @unlink($this->fichier);
});

it('reprend les abonnes de l ancien site, sans adresse invalide ni doublon', function () {
    $migrateur = new LegacyMigrator(new LegacySampler(0, null, true));
    $migrateur->migrateNewsletter();

    $ariane = NewsletterMail::where('email', 'ariane@example.com')->sole();
    $parti = NewsletterMail::where('email', 'parti@example.com')->sole();

    expect(NewsletterMail::count())->toBe(2)
        ->and($ariane->brand)->toBe('ub')
        ->and($ariane->ip)->toBe('1.2.3.4')
        ->and($ariane->created_at->toDateString())->toBe('2014-03-02')
        ->and($ariane->desabonne_at)->toBeNull()
        ->and($parti->desabonne_at)->not->toBeNull()
        ->and($migrateur->counts())->toMatchArray([
            'abonnes_newsletter' => 2,
            'abonnes_adresse_invalide' => 1,
            'abonnes_doublons' => 1,
        ]);
});

it('detaille les valeurs coupees par table et colonne', function () {
    // L'ecouteur est pose une fois par processus ; chaque test a une
    // application neuve, il faut donc le reposer.
    (new ReflectionProperty(LegacyMigrator::class, 'ecoute'))->setValue(null, false);
    new LegacyMigrator(new LegacySampler(0, null, true));

    // SQLite ne donne pas la longueur des varchar : on la fixe, comme MySQL
    // la renverrait (users.zipcode = varchar(20)).
    $longueurs = new ReflectionProperty(LegacyMigrator::class, 'longueurs');
    $longueurs->setValue(null, ['users' => ['zipcode' => 20]] + $longueurs->getValue());

    $avant = LegacyMigrator::detailTroncatures()['users.zipcode']['nombre'] ?? 0;

    User::factory()->create(['zipcode' => str_repeat('9', 40)]);

    $detail = LegacyMigrator::detailTroncatures()['users.zipcode'] ?? null;

    expect($detail)->not->toBeNull()
        ->and($detail['nombre'])->toBe($avant + 1)
        ->and(User::latest('id')->first()->zipcode)->toHaveLength(20);
});

it('repare un octet orphelin laisse par un varchar legacy coupe', function () {
    (new ReflectionProperty(LegacyMigrator::class, 'ecoute'))->setValue(null, false);
    new LegacyMigrator(new LegacySampler(0, null, true));

    // us_cp « Paris 19 » + premier octet d'un « è » coupe (us_id 14765).
    $user = User::factory()->create(['zipcode' => hex2bin('506172697320313920c3')]);

    expect($user->fresh()->zipcode)->toBe('Paris 19')
        ->and(LegacyMigrator::detailTroncatures())->toHaveKey('users.zipcode (octets invalides repares)');
});
