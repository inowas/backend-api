<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Model\DomainEvent;
use App\Domain\User\Aggregate\UserAggregate;

final class UserHasBeenArchived extends DomainEvent
{
    /**
     * @param string $aggregateId
     * @return UserHasBeenArchived
     * @throws \Exception
     */
    public static function fromParams(string $aggregateId): UserHasBeenArchived
    {
        return new self($aggregateId, UserAggregate::NAME, self::getEventNameFromClassname(), []);
    }
}
