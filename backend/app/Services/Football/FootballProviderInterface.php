<?php

declare(strict_types=1);

namespace App\Services\Football;

/**
 * Contrat commun à tous les fournisseurs de données football.
 * Chaque provider retourne des fixtures au format normalisé COTA.
 */
interface FootballProviderInterface
{
    /** Nom lisible du provider (pour les logs) */
    public function name(): string;

    /** Vérifie si le provider est disponible (quota OK, clé configurée…) */
    public function isAvailable(): bool;

    /**
     * Fixtures du jour (ou date donnée).
     * Retourne un tableau de fixtures au format normalisé, ou [] si rien.
     */
    public function getFixtures(?string $date = null): array;

    /** Matchs en direct. */
    public function getLiveMatches(): array;
}
