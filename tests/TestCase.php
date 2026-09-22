<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /*
         | Les vues appellent @vite. Sans serveur de developpement ni build,
         | Laravel leve « Vite manifest not found » et chaque page rend 500 :
         | la suite echouait des que `npm run dev` s'arretait, sans rapport
         | avec le code teste. Les balises Vite sont neutralisees en test.
         */
        $this->withoutVite();
    }
}
