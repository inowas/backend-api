<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Model\Command;

final class RevokeLoginTokenCommand extends Command
{
    private string $userId;

    public static function fromPayload(array $payload): self
    {
        $self = new self();
        $self->userId = $payload['user_id'];
        return $self;
    }

    public function userId(): string
    {
        return $this->userId;
    }
}
