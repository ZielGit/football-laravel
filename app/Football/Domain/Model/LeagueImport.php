<?php

declare(strict_types=1);

namespace App\Football\Domain\Model;

use DateTimeImmutable;

/** Estado de la importación de una liga (agregado de seguimiento). */
final readonly class LeagueImport
{
    public function __construct(
        public int $competitionId,
        public ImportStatus $status,
        public DateTimeImmutable $updatedAt,
        public ?string $message = null,
        public ?int $retryAfterSeconds = null,
        public ?int $teamsImported = null,
        public ?int $playersImported = null,
        public ?int $requestedBy = null,
        public ?DateTimeImmutable $finishedAt = null,
    ) {}

    public static function queued(int $competitionId, ?int $requestedBy = null): self
    {
        return new self($competitionId, ImportStatus::Queued, new DateTimeImmutable, requestedBy: $requestedBy);
    }

    public function running(): self
    {
        return new self($this->competitionId, ImportStatus::Running, new DateTimeImmutable, requestedBy: $this->requestedBy);
    }

    public function waitingForRateLimit(int $retryAfterSeconds): self
    {
        return new self(
            $this->competitionId,
            ImportStatus::WaitingRateLimit,
            new DateTimeImmutable,
            'Límite de peticiones de football-data alcanzado; se reintentará automáticamente.',
            $retryAfterSeconds,
            requestedBy: $this->requestedBy,
        );
    }

    public function completed(int $teams, int $players): self
    {
        $now = new DateTimeImmutable;

        return new self($this->competitionId, ImportStatus::Completed, $now, null, null, $teams, $players, $this->requestedBy, $now);
    }

    public function failed(string $message): self
    {
        $now = new DateTimeImmutable;

        return new self($this->competitionId, ImportStatus::Failed, $now, $message, requestedBy: $this->requestedBy, finishedAt: $now);
    }

    /** Una importación activa que no se actualiza hace mucho se considera abandonada. */
    public function isActive(int $abandonedAfterMinutes = 15): bool
    {
        return $this->status->isActive()
            && $this->updatedAt > new DateTimeImmutable("-{$abandonedAfterMinutes} minutes");
    }

    public function isFresh(int $ttlMinutes): bool
    {
        return $this->status === ImportStatus::Completed
            && $this->finishedAt !== null
            && $this->finishedAt > new DateTimeImmutable("-{$ttlMinutes} minutes");
    }
}
