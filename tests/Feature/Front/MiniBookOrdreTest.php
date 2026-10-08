<?php

use App\Models\User;

it('suit l ordre du book : premiere galerie, puis ordre de ses visuels', function () {
    $creatif = User::factory()->create(['login' => 'ordre-mini', 'brand' => 'ub']);
    $creatif->bookSetting()->create(['theme' => 'mdl_2014_responsive', 'diffuse_web' => true, 'diffuse_ub' => true]);
    $seconde = $creatif->galleries()->create(['name' => 'B', 'status' => 'published', 'position' => 2]);
    $premiere = $creatif->galleries()->create(['name' => 'A', 'status' => 'published', 'position' => 1]);
    $b1 = $seconde->media()->create(['user_id' => $creatif->id, 'filename' => 'b1.jpg', 'status' => 'published']);
    $a1 = $premiere->media()->create(['user_id' => $creatif->id, 'filename' => 'a1.jpg', 'status' => 'published']);
    $a2 = $premiere->media()->create(['user_id' => $creatif->id, 'filename' => 'a2.jpg', 'status' => 'published',
        'video_url' => 'https://vimeo.com/353558438']);
    $premiere->update(['media_order' => [$a2->id, $a1->id]]);

    $visuels = app(App\Repository\BookRepository::class)->parLogin('ordre-mini')->visuelsMiniBook();

    expect($visuels->pluck('filename')->all())->toBe(['a2.jpg', 'a1.jpg', 'b1.jpg'])
        ->and($visuels->first()->video()->lecteur())->toContain('player.vimeo.com/video/353558438');
});
