<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Model\DomainEvent;
use App\Domain\User\Aggregate\UserAggregate;
use Exception;

final class UserHasBeenDeleted extends DomainEvent
{
    /**
     * @param string $aggregateId
     * @return UserHasBeenDeleted
     * @throws Exception
     */
    public static function fromParams(string $aggregateId): UserHasBeenDeleted
    {
        $self = new self($aggregateId, UserAggregate::NAME, self::getEventNameFromClassname(), []);
        return $self;
    }
}
