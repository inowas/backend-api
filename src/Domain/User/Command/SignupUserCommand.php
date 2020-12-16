<?php

declare(strict_types=1);

namespace App\Domain\User\Command;

use App\Model\Command;

class SignupUserCommand extends Command
{
    private string $name;
    private string $email;
    private string $password;

    public static function fromParams(string $name, string $email, string $password): self
    {
        $self = new self();
        $self->name = $name;
        $self->email = $email;
        $self->password = $password;
        return $self;
    }

    public static function fromPayload(array $payload): self
    {
        $self = new self();
        $self->name = $payload['name'];
        $self->email = $payload['email'];
        $self->password = $payload['password'];
        return $self;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function password(): string
    {
        return $this->password;
    }
}
