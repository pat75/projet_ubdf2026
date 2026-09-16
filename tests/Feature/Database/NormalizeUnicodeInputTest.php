<?php

use App\Http\Middleware\NormalizeUnicodeInput;
use Illuminate\Http\Request;

function passThrough(array $input): array
{
    $request = Request::create('/', 'POST', $input);

    (new NormalizeUnicodeInput)->handle($request, fn ($r) => response(''));

    return $request->input();
}

it('recompose un texte en forme decomposee', function () {
    // « é » ecrit « e » + accent combinant, comme le produit macOS.
    $decomposed = "Am\u{0065}\u{0301}lie Falie\u{0300}re";

    $result = passThrough(['name' => $decomposed]);

    expect($result['name'])->toBe('Amélie Falière')
        ->and($result['name'])->not->toBe($decomposed);
});

it('convertit une entree Windows-1252 en UTF-8', function () {
    $latin1 = mb_convert_encoding('Scénographe', 'Windows-1252', 'UTF-8');

    expect(mb_check_encoding($latin1, 'UTF-8'))->toBeFalse()
        ->and(passThrough(['job' => $latin1])['job'])->toBe('Scénographe');
});

it('laisse intact un texte deja correct et normalise', function () {
    $input = ['name' => 'Amélie Falière', 'city' => 'Paris', 'note' => '日本語'];

    expect(passThrough($input))->toBe($input);
});

it('traite les tableaux imbriques', function () {
    $result = passThrough(['book' => ['tags' => ["cr\u{0065}\u{0301}ation"]]]);

    expect($result['book']['tags'][0])->toBe('création');
});

it('preserve les valeurs non textuelles', function () {
    $result = passThrough(['count' => '12', 'flag' => '1']);

    expect($result['count'])->toBe('12')->and($result['flag'])->toBe('1');
});
