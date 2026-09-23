<?php

use Illuminate\Support\Facades\Schedule;

/*
 | Relances d'abonnement : une fois par jour, en fin de matinee. Le legacy
 | les lancait a la main depuis une URL d'administration, par tranches de
 | 500 comptes. `withoutOverlapping` evite qu'une execution longue en
 | croise une autre.
 */
Schedule::command('ubdf:relancer-formules')
    ->dailyAt('11:00')
    ->timezone('Europe/Paris')
    ->withoutOverlapping();
