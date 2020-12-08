<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Model\DomainEvent;
use App\Domain\User\Aggregate\UserAggregate;
use Exception;

final class UserHasBeenDemoted extends DomainEvent
{
    private string $role;

    /**
     * @param string $aggregateId
     * @param string $role
     * @return UserHasBeenPromoted
     * @throws Exception
     */
    public static function fromParams(string $aggregateId, string $role): UserHasBeenDemoted
    {
        $self = new self($aggregateId, UserAggregate::NAME, self::getEventNameFromClassname(), ['role' => $role]);
        $self->role = $role;
        return $self;
    }

    public function role(): string
    {
        if (!$this->role) {
            $this->role = $this->payload['role'];
        }

        return $this->role;
    }
}
