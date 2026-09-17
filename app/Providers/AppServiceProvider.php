<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         | Intervention Image, pilote choisi a l'execution.
         |
         | Imagick rend mieux les degrades et gere les profils de couleur ;
         | GD est toujours la. Le MAMP de developpement n'a pas Imagick, le
         | serveur de production peut l'avoir : le choix se fait donc sur ce
         | qui est charge, sans configuration a tenir a jour.
         */
        $this->app->singleton(ImageManager::class, fn () => new ImageManager(
            extension_loaded('imagick') ? new ImagickDriver : new GdDriver
        ));
    }

    public function boot(): void
    {
        //
    }
}
