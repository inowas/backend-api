<?php

declare(strict_types=1);

namespace App\Domain\User\Event;

use App\Model\DomainEvent;
use App\Domain\User\Aggregate\UserAggregate;
use Exception;

final class UserHasBeenEnabled extends DomainEvent
{
    /**
     * @param string $aggregateId
     * @return self
     * @throws Exception
     */
    public static function fromParams(string $aggregateId): self
    {
        return new self($aggregateId, UserAggregate::NAME, self::getEventNameFromClassname(), []);
    }
}
