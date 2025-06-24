<?php

namespace App\Entity\Remote;

use App\Repository\Remote\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    const ACTIONS = [
        'PLATFORM_ADD',
        'PLATFORM_VERIFY',
        'PLATFORM_PULL',
        'PLATFORM_DISABLE',
        'PLATFORM_ENABLE',
        'SITE_ADD',
        'SITE_VERIFY',
        'SITE_CLEAR_CACHE',
        'SITE_RUN_CRON',
        'SITE_DB_UPDATES',
        'SITE_BACKUP',
        'SITE_CLONE',
        'SITE_RESET_PASSWORD',
        'SITE_DISABLE',
        'SITE_ENABLE',
        'SITE_DELETE',
    ];

    const STATUSES = [
        'PENDING',
        'RUNNING',
        'SUCCESS',
        'WARNING',
        'FAILED',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $started_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $ended_at = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $source_entity = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\Column(nullable: true)]
    private ?array $parameters = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logs = null;

    #[ORM\Column(nullable: true)]
    private ?array $results = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getCreatedAtFormatted(): string
    {
        $formatted = $this->created_at->format('Y-m-d H:i:s');
        if (empty($formatted)) {
            $formatted = '-';
        }
        return $formatted;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->started_at;
    }

    public function getStartedAtFormatted(): string
    {
        $formatted = $this->started_at->format('Y-m-d H:i:s');
        if (empty($formatted)) {
            $formatted = '-';
        }
        return $formatted;
    }

    public function setStartedAt(?\DateTimeInterface $started_at): static
    {
        $this->started_at = $started_at;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->ended_at;
    }

    public function getEndedAtFormatted(): string
    {
        $formatted = $this->ended_at->format('Y-m-d H:i:s');
        if (empty($formatted)) {
            $formatted = '-';
        }
        return $formatted;
    }

    public function setEndedAt(?\DateTimeInterface $ended_at): static
    {
        $this->ended_at = $ended_at;

        return $this;
    }

    public function getSourceEntity(): ?int
    {
        return $this->source_entity;
    }

    public function setSourceEntity(?int $source_entity): static
    {
        $this->source_entity = $source_entity;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public static function getActionLabel(string $action): ?string
    {
        $labels = [
            'PLATFORM_ADD' => 'Add Platform',
            'PLATFORM_VERIFY' => 'Verify Platform',
            'PLATFORM_PULL' => 'Pull Platform',
            'PLATFORM_DISABLE' => 'Disable Platform',
            'PLATFORM_ENABLE' => 'Enable Platform',
            'SITE_ADD' => 'Add Site',
            'SITE_VERIFY' => 'Verify Site',
            'SITE_CLEAR_CACHE' => 'Clear Cache',
            'SITE_RUN_CRON' => 'Run Cron',
            'SITE_DB_UPDATES' => 'Run DB Updates',
            'SITE_BACKUP' => 'Backup',
            'SITE_CLONE' => 'Clone',
            'SITE_RESET_PASSWORD' => 'Reset Password',
            'SITE_DISABLE' => 'Disable Site',
            'SITE_ENABLE' => 'Enable Site',
            'SITE_DELETE' => 'Delete Site',
        ];

        return $labels[$action] ?? null;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLogs(): ?string
    {
        return $this->logs;
    }

    public function setLogs(?string $logs): static
    {
        $this->logs = $logs;

        return $this;
    }

    public function getResults(): ?array
    {
        return $this->results;
    }

    public function setResults(?array $results): static
    {
        $this->results = $results;

        return $this;
    }
}
