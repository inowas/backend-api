<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Model\Command;

final class PromoteUserCommand extends Command
{
    private string $userId;
    private string $role;

    public static function fromParams(string $userId, string $role): PromoteUserCommand
    {
        return self::fromPayload(["user_id" => $userId, "role" => $role]);
    }

    public static function fromPayload(array $payload): PromoteUserCommand
    {
        $self = new self();
        $self->userId = $payload['user_id'];
        $self->role = $payload['role'];
        return $self;
    }

    public function userId(): string
    {
        return $this->userId;
    }

    public function role(): string
    {
        return $this->role;
    }
}
