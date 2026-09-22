<?php

use Illuminate\Http\Request;

// php artisan tinker --execute="$(cat _outils/erreur_book.php)" avec URL en variable d'env
foreach (explode(' ', getenv('URLS')) as $u) {
    $r = app()->handle(Request::create($u));
    $e = $r->exception;
    $m = $e ? preg_replace('/\(View: .*$/', '', $e->getMessage()) : '';
    // Remonte au gabarit source depuis le fichier compile.
    $src = '';
    if ($e) {
        foreach (array_merge([['file' => $e->getFile(), 'line' => $e->getLine()]], $e->getTrace()) as $t) {
            if (isset($t['file']) && str_contains($t['file'], 'storage/framework/views')) {
                $premiere = fgets(fopen($t['file'], 'r'));
                if (preg_match('#2011_html_pages_v2/(\S+)#', $premiere, $x)) {
                    $src = $x[1].':'.$t['line'];
                    break;
                }
            }
        }
    }
    printf("%s %d %s %s\n", $u, $r->getStatusCode(), $src, substr($m, 0, 170));
}
