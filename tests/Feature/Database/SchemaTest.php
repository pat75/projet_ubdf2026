<?php

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('cree un creatif avec son book et resout son sous-domaine', function () {
    $category = Category::create(['slug' => 'graphiste', 'name' => 'Graphiste']);

    $user = User::create([
        'login' => 'pat10',
        'email' => 'pat10@example.test',
        'password' => Hash::make('secret'),
        'category_id' => $category->id,
    ]);

    $gallery = Gallery::create([
        'user_id' => $user->id,
        'name' => 'Illustrations',
        'status' => 'published',
    ]);

    Media::create([
        'user_id' => $user->id,
        'gallery_id' => $gallery->id,
        'filename' => 'visuel.jpg',
        'status' => 'published',
    ]);

    expect($user->bookUrl())->toBe('https://pat10.'.config('ubdf.book_domain'))
        ->and($user->galleries()->published()->count())->toBe(1)
        ->and($gallery->media)->toHaveCount(1)
        ->and($user->category->slug)->toBe('graphiste');
});

it('impose un login unique', function () {
    User::create(['login' => 'pat10', 'email' => 'a@example.test', 'password' => 'x']);
    User::create(['login' => 'pat10', 'email' => 'b@example.test', 'password' => 'x']);
})->throws(Illuminate\Database\QueryException::class);
