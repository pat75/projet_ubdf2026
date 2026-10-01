<?php

use Illuminate\Support\Facades\File;

/*
| Toute chaine __('…') ecrite en dur dans les vues du portail, de l'espace creatif et du compte visiteur doit
| avoir sa traduction dans chaque langue servie par Dustfolio. Le francais
| est la langue source (la cle elle-meme) : il n'a pas de catalogue a tenir.
|
| Ajouter une langue a Dustfolio (config/marques.php) fait donc echouer ce
| test tant que lang/<code>.json n'est pas complet.
*/

/** @return array<string, string> cle => vue ou elle apparait */
function clesDuPortail(): array
{
    $vues = collect([
        resource_path('views/partials'),
        resource_path('views/components/portail'),
        resource_path('views/front'),
        resource_path('views/espace'),
        resource_path('views/layouts'),
        resource_path('views/livewire/espace'),
        resource_path('views/livewire/espace/partials'),
        resource_path('views/livewire/visiteur'),
        resource_path('views/livewire/memo'),
        resource_path('views/visiteur'),
        resource_path('views/components/espace'),
        resource_path('views/partials/espace'),
        app_path('Livewire/Espace'),
        app_path('Livewire/Visiteur'),
        app_path('Livewire/Memo'),
        app_path('Http/Controllers/Espace'),
        app_path('Services/Espace'),
    ])->flatMap(fn (string $dossier) => File::files($dossier))
        ->push(new SplFileInfo(resource_path('views/components/book-card.blade.php')))
        ->filter(fn (SplFileInfo $f) => str_ends_with($f->getFilename(), '.php'));

    $cles = [];

    foreach ($vues as $vue) {
        preg_match_all("/__\(\s*'((?:[^'\\\\]|\\\\.)*)'/", File::get($vue->getPathname()), $m);

        foreach ($m[1] as $cle) {
            $cles[str_replace("\\'", "'", $cle)] ??= $vue->getFilename();
        }
    }

    return $cles;
}

it('traduit les textes du portail dans chaque langue de Dustfolio', function () {
    $langues = array_diff(config('marques.marques.df.langues'), ['fr']);
    $cles = clesDuPortail();

    expect($cles)->not->toBeEmpty();

    foreach ($langues as $langue) {
        $catalogue = json_decode(File::get(lang_path($langue.'.json')), true);

        $manquantes = collect($cles)
            ->reject(fn (string $vue, string $cle) => array_key_exists($cle, $catalogue))
            ->map(fn (string $vue, string $cle) => "{$vue} : {$cle}")
            ->values()
            ->all();

        expect($manquantes)->toBe([], "Traductions manquantes dans lang/{$langue}.json");
    }
});
