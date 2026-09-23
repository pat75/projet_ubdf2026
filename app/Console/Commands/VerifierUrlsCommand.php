<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Verifie que chaque URL publique du legacy trouve une route.
 *
 * Les URL du .htaccess de 2019 sont indexees depuis des annees : une seule
 * oubliee, et ce sont des visiteurs perdus. La commande lit les regles de
 * reecriture, en tire une URL d'exemple, et demande au routeur s'il sait
 * y repondre — sans toucher a la base ni rendre les pages.
 */
class VerifierUrlsCommand extends Command
{
    protected $signature = 'ubdf:verifier-urls {--tout : afficher aussi les URL couvertes}';

    protected $description = 'Confronte les URL du .htaccess du legacy aux routes actuelles';

    /** Regles qui ne servent pas le public : elles n'ont pas a etre reprises. */
    private const IGNOREES = [
        'phpthumb', 'phpThumb', 'app_laravel', 'redirect_LinkedIn', 'action.php',
        'users_2/', 'ajax_wp_actu', 'inc_public', 'wp-', 'admin_',
    ];

    public function handle(Router $router): int
    {
        $htaccess = config('ubdf.legacy_path').'/.htaccess';

        if (! is_file($htaccess)) {
            $this->components->error('.htaccess du legacy introuvable : '.$htaccess);

            return self::FAILURE;
        }

        $manquantes = [];
        $couvertes = 0;

        foreach ($this->urls($htaccess) as $url) {
            if ($this->couverte($router, $url)) {
                $couvertes++;

                if ($this->option('tout')) {
                    $this->line('  <fg=green>ok</> '.$url);
                }

                continue;
            }

            $manquantes[] = $url;
        }

        $this->newLine();
        $this->components->info($couvertes.' URL couvertes, '.count($manquantes).' sans route.');

        foreach ($manquantes as $url) {
            $this->line('  <fg=red>manque</> '.$url);
        }

        return $manquantes === [] ? self::SUCCESS : self::FAILURE;
    }

    /** @return list<string> URL d'exemple, une par regle de reecriture */
    public function urls(string $htaccess): array
    {
        $urls = [];

        foreach (file($htaccess) as $ligne) {
            if (! preg_match('#^RewriteRule\s+\^([^\s]+)#', trim($ligne), $trouve)) {
                continue;
            }

            $motif = $trouve[1];

            foreach (self::IGNOREES as $ignoree) {
                if (str_contains($motif, $ignoree)) {
                    continue 2;
                }
            }

            $url = $this->exemple($motif);

            if ($url !== null) {
                $urls[$url] = $url;
            }
        }

        return array_values($urls);
    }

    /** Transforme un motif de reecriture en URL concrete, ou null si impossible. */
    private function exemple(string $motif): ?string
    {
        $url = rtrim($motif, '$');

        // Une regle qui n'est qu'un groupe (`^(.*)$`) attrape tout : elle ne
        // designe pas une URL precise a reprendre.
        if (preg_match('#^\([^()]*\)$#', $url)) {
            return null;
        }

        // Groupes facultatifs, comme `(/?)` : on les retire.
        $url = preg_replace('#\([^()]*\?\)#', '', $url);

        // Alternatives : on prend la premiere.
        $url = preg_replace_callback('#\(([^()]*\|[^()]*)\)#', fn ($m) => explode('|', $m[1])[0], $url);

        // Groupes de chiffres a longueur fixe : autant de chiffres qu'il faut.
        $url = preg_replace_callback('#\(\[0-9\]\{(\d+)(?:,\d+)?\}\)#', fn ($m) => str_repeat('4', (int) $m[1]), $url);

        // Groupes restants : une valeur plausible selon ce qu'ils acceptent.
        $url = preg_replace_callback('#\(([^()]+)\)#', function (array $m) {
            $contenu = $m[1];

            return match (true) {
                str_contains($contenu, '0-9') && ! str_contains($contenu, 'a-z') => '42',
                default => 'exemple',
            };
        }, $url);

        $url = str_replace(['\\', '.*', '*', '?', '+'], '', $url);

        return str_contains($url, '(') || $url === '' ? null : '/'.ltrim($url, '/');
    }

    /**
     * Un motif ne dit pas toujours ce qu'il accepte (`(.*)` peut etre un
     * identifiant, un login ou un nombre). On essaie donc les trois formes
     * a la place du jeton d'exemple : si l'une trouve une route, l'URL est
     * couverte.
     */
    private function couverte(Router $router, string $url): bool
    {
        $candidats = [$url];

        foreach (['42', 'a', '0'] as $valeur) {
            $candidats[] = str_replace('exemple', $valeur, $url);
        }

        foreach (array_unique($candidats) as $candidat) {
            if ($this->routeExiste($router, $candidat)) {
                return true;
            }
        }

        return false;
    }

    private function routeExiste(Router $router, string $url): bool
    {
        try {
            $router->getRoutes()->match(Request::create($url, 'GET'));

            return true;
        } catch (NotFoundHttpException) {
            return false;
        } catch (MethodNotAllowedHttpException) {
            // La route existe, sur un autre verbe : elle est bien reprise.
            return true;
        } catch (HttpException|UrlGenerationException) {
            return true;
        }
    }
}
