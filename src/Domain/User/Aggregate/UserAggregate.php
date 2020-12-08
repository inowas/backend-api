<?php

declare(strict_types=1);

namespace App\Domain\User\Aggregate;

use App\Domain\User\Event\UserHasBeenDemoted;
use App\Domain\User\Event\UserHasBeenPromoted;
use App\Domain\User\Event\UserHasBeenArchived;
use App\Domain\User\Event\UserHasBeenCreated;
use App\Domain\User\Event\UserHasBeenDeleted;
use App\Domain\User\Event\UserHasBeenReactivated;
use App\Domain\User\Event\UsernameHasBeenChanged;
use App\Domain\User\Event\UserPasswordHasBeenChanged;
use App\Domain\User\Event\UserProfileHasBeenChanged;
use App\Model\Aggregate;

class UserAggregate extends Aggregate
{
    public const NAME = 'user';

    public static array $registeredEvents = [
        UserHasBeenArchived::class,
        UserHasBeenCreated::class,
        UserHasBeenDeleted::class,
        UserHasBeenDemoted::class,
        UserHasBeenPromoted::class,
        UserHasBeenReactivated::class,
        UsernameHasBeenChanged::class,
        UserPasswordHasBeenChanged::class,
        UserProfileHasBeenChanged::class
    ];
}
