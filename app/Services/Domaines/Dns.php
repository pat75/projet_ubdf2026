<?php

namespace App\Services\Domaines;

/** Lecture DNS publique : isolee pour etre remplacee dans les tests. */
class Dns
{
    /** @return list<string> adresses IPv4 du domaine */
    public function ipv4(string $nom): array
    {
        return array_column(@dns_get_record($nom, DNS_A) ?: [], 'ip');
    }

    /** @return list<string> valeurs TXT publiees pour ce nom */
    public function txt(string $nom): array
    {
        return array_column(@dns_get_record($nom, DNS_TXT) ?: [], 'txt');
    }
}
