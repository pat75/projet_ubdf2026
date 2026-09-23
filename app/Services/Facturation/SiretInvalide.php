<?php

namespace App\Services\Facturation;

use RuntimeException;

/** Le numero saisi n'a pas la forme d'un SIRET (longueur ou cle de Luhn). */
class SiretInvalide extends RuntimeException {}
