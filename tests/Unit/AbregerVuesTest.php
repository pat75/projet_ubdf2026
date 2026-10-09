<?php

use App\View\Components\BookCard;

it('abrege le nombre de vues de la carte', function (int $nombre, string $attendu) {
    expect(BookCard::abreger($nombre))->toBe($attendu);
})->with([
    [0, ''],
    [999, '999'],
    [1000, '1k'],
    [4227, '4,2k'],
    [9960, '10k'],
    [53458, '53k'],
    [183688, '184k'],
    [999_499, '999k'],
    [999_500, '1M'],
    [1_250_000, '1,3M'],
]);
