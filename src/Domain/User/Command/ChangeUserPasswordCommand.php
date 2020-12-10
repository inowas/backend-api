<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Model\Command;

class ChangeUserPasswordCommand extends Command
{
    private ?string $userId;

    private string $password;

    private string $newPassword;

    public static function fromPayload(array $payload): self
    {
        $self = new self();
        $self->userId = $payload['user_id'] ?? null;
        $self->password = $payload['password'];
        $self->newPassword = $payload['new_password'];
        return $self;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function newPassword(): string
    {
        return $this->newPassword;
    }

    public function userId(): ?string
    {
        return $this->userId;
    }
}
