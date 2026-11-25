<?php

declare(strict_types=1);

namespace App\Football\Domain\Gateway;

use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalResourceNotFound;
use App\Football\Domain\Exception\ExternalServiceUnavailable;
use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\LeagueSnapshot;

/**
 * Puerto de salida hacia football-data.org.
 * El dominio y la aplicación solo conocen esta interfaz.
 */
interface FootballDataGateway
{
    /**
     * @return list<Competition>
     *
     * @throws ExternalRateLimitExceeded
     * @throws ExternalServiceUnavailable
     */
    public function fetchCompetitions(): array;

    /**
     * @throws ExternalRateLimitExceeded
     * @throws ExternalResourceNotFound
     * @throws ExternalServiceUnavailable
     */
    public function fetchLeagueSnapshot(int $competitionId): LeagueSnapshot;
}
