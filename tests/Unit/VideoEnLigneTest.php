<?php

use App\Support\VideoEnLigne;

it('reconnait les liens YouTube', function (string $lien) {
    $video = VideoEnLigne::depuis($lien);

    expect($video?->plateforme)->toBe('youtube')
        ->and($video->identifiant)->toBe('dQw4w9WgXcQ')
        ->and($video->lecteur())->toStartWith('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
})->with([
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'https://m.youtube.com/watch?feature=share&v=dQw4w9WgXcQ',
    'youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtu.be/dQw4w9WgXcQ?si=xyz',
    'https://www.youtube.com/shorts/dQw4w9WgXcQ',
    'https://www.youtube.com/embed/dQw4w9WgXcQ?start=3',
]);

it('reconnait les liens Vimeo', function (string $lien) {
    expect(VideoEnLigne::depuis($lien)?->lien())->toBe('https://vimeo.com/76979871');
})->with([
    'https://vimeo.com/76979871',
    'https://player.vimeo.com/video/76979871?h=abc',
    'https://vimeo.com/channels/staffpicks/76979871',
]);

it('ecarte les autres liens', function (string $lien) {
    expect(VideoEnLigne::depuis($lien))->toBeNull();
})->with([
    '',
    'https://example.com/watch?v=dQw4w9WgXcQ',
    'https://www.youtube.com/watch?v=court',
    'https://vimeo.com/about',
    'javascript:alert(1)',
]);
