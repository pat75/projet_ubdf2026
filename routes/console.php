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

// Archives « Mes donnees » expirees (DataExport::CONSERVATION_JOURS).
Schedule::command('ubdf:purger-exports')->dailyAt('04:00')->timezone('Europe/Paris')->withoutOverlapping();

/*
 | Sauvegardes : base de donnees et code, chaque nuit. Les visuels des books
 | en sont exclus (voir config/backup.php) et relevent d'une synchronisation
 | de fichiers a part.
 */
Schedule::command('backup:clean')->dailyAt('02:30')->timezone('Europe/Paris');
Schedule::command('backup:run')->dailyAt('03:00')->timezone('Europe/Paris');
Schedule::command('backup:monitor')->dailyAt('08:00')->timezone('Europe/Paris');
