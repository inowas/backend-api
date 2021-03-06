<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Model\Command;

class ChangeUsernameCommand extends Command
{
    private ?string $userId;
    private string $username;

    public static function fromPayload(array $payload): self
    {
        $self = new self();
        $self->userId = $payload['user_id'] ?? null;
        $self->username = $payload['username'];
        return $self;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }
}
