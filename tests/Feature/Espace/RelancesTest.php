<?php

use App\Mail\RelanceFormule;
use App\Models\SubscriptionReminder;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

function abonneJusqua(string $echeance, array $attrs = []): User
{
    // plan_started_at + plan_months donne l'echeance voulue.
    return User::factory()->create($attrs + [
        'plan' => 1,
        'plan_started_at' => \Illuminate\Support\Carbon::parse($echeance)->subMonths(12),
        'plan_months' => 12,
    ]);
}

it('relance a J-5 puis le jour de l echeance', function () {
    $dans5jours = abonneJusqua(now()->addDays(5)->toDateString(), ['email' => 'cinq@example.test']);
    $aujourdhui = abonneJusqua(now()->toDateString(), ['email' => 'zero@example.test']);
    abonneJusqua(now()->addDays(9)->toDateString(), ['email' => 'plus-tard@example.test']);

    $this->artisan('ubdf:relancer-formules')->assertSuccessful();

    Mail::assertQueued(RelanceFormule::class, fn ($m) => $m->hasTo('cinq@example.test') && $m->joursRestants === 5);
    Mail::assertQueued(RelanceFormule::class, fn ($m) => $m->hasTo('zero@example.test') && $m->joursRestants === 0);
    Mail::assertNotQueued(RelanceFormule::class, fn ($m) => $m->hasTo('plus-tard@example.test'));

    expect(SubscriptionReminder::count())->toBe(2)
        ->and(SubscriptionReminder::where('user_id', $dans5jours->id)->sole()->days_before)->toBe(5)
        ->and($aujourdhui->fresh()->plan)->toBe(1);
});

it('ne relance jamais deux fois la meme echeance', function () {
    abonneJusqua(now()->addDays(5)->toDateString(), ['email' => 'cinq@example.test']);

    $this->artisan('ubdf:relancer-formules');
    $this->artisan('ubdf:relancer-formules');

    Mail::assertQueuedCount(1);
    expect(SubscriptionReminder::count())->toBe(1);
});

it('ne relance pas les formules gratuites', function () {
    User::factory()->create(['plan' => 0, 'email' => 'gratuit@example.test']);

    $this->artisan('ubdf:relancer-formules');

    Mail::assertNothingQueued();
});

it('ecrit au createur avec le domaine de sa marque', function () {
    $df = abonneJusqua(now()->addDays(5)->toDateString(), ['email' => 'df@example.test', 'brand' => 'df']);

    $this->artisan('ubdf:relancer-formules');

    Mail::assertQueued(RelanceFormule::class, function (RelanceFormule $mail) use ($df) {
        $rendu = $mail->render();

        return $mail->hasTo($df->email)
            && str_contains($rendu, 'dustfolio.com')
            && str_contains($rendu, '/espace/formule');
    });
});

it('relance un abonnement echu au plus une fois tous les 7 jours', function () {
    $echu = abonneJusqua(now()->subDays(20)->toDateString(), ['email' => 'echu@example.test']);
    $relances = app(\App\Services\Paiement\Relances::class);

    expect($relances->relancerEchu($echu))->toBeTrue();

    $this->travel(3)->days();
    expect($relances->relancerEchu($echu))->toBeFalse();

    $this->travel(4)->days();
    expect($relances->relancerEchu($echu))->toBeTrue();

    Mail::assertQueued(RelanceFormule::class, fn ($m) => $m->hasTo('echu@example.test') && $m->joursRestants === -1);
    Mail::assertQueuedCount(2);
    expect(SubscriptionReminder::where('days_before', -1)->count())->toBe(2);
});

it('compte les abonnements repris apres une relance', function () {
    $relances = app(\App\Services\Paiement\Relances::class);
    $repris = abonneJusqua(now()->subDays(10)->toDateString(), ['email' => 'repris@example.test']);
    $toujoursEchu = abonneJusqua(now()->subDays(10)->toDateString(), ['email' => 'echu@example.test']);
    abonneJusqua(now()->addMonths(3)->toDateString(), ['email' => 'jamais-relance@example.test']);

    $relances->relancerEchu($repris);
    $relances->relancerEchu($toujoursEchu);

    // Renouvellement : nouvelle echeance dans un an.
    $repris->update(['plan_started_at' => now(), 'plan_months' => 12]);

    expect($relances->reprises())->toBe(1);
});
