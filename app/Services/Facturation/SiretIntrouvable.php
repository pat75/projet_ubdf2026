<?php

namespace App\Services\Facturation;

use RuntimeException;

/** Le SIRET est bien forme, mais l'annuaire de l'Etat ne le connait pas. */
class SiretIntrouvable extends RuntimeException {}
