<?php

namespace App\Services\IA;

use RuntimeException;

/** NVIDIA injoignable : l'analyse est decalee (Nvidia::PAUSE), le visuel reste en attente. */
class NvidiaEnPause extends RuntimeException {}
