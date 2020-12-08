<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Model\DomainEvent;
use App\Domain\User\Aggregate\UserAggregate;
use Exception;

final class UsernameHasBeenChanged extends DomainEvent
{
    private string $username;

    /**
     * @param string $aggregateId
     * @param string $username
     * @return UsernameHasBeenChanged
     * @throws Exception
     */
    public static function fromParams(string $aggregateId, string $username): UsernameHasBeenChanged
    {
        $self = new self($aggregateId, UserAggregate::NAME, self::getEventNameFromClassname(), [
            'username' => $username,
        ]);

        $self->username = $username;
        return $self;
    }

    public function username(): ?string
    {
        if (!$this->username) {
            $this->username = $this->payload['username'];
        }

        return $this->username;
    }
}
