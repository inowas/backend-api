<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Model\DomainEvent;
use App\Domain\User\Aggregate\UserAggregate;
use Exception;

final class UserHasBeenReactivated extends DomainEvent
{
    /**
     * @param string $aggregateId
     * @return UserHasBeenReactivated
     * @throws Exception
     */
    public static function fromParams(string $aggregateId): UserHasBeenReactivated
    {
        return new self($aggregateId, UserAggregate::NAME, self::getEventNameFromClassname(), []);
    }
}
