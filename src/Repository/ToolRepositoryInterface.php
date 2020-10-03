<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;

interface ToolRepositoryInterface
{
    public function getTool(string $tool, User $user, bool $isPublic, bool $isArchived): array;
}
