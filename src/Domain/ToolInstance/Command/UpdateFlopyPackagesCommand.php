<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\Command;

use App\Model\Command;
use App\Model\Modflow\Packages;
use Exception;

class UpdateFlopyPackagesCommand extends Command
{
    /** @var string */
    private string $id;

    /** @var array */
    private array $packages;

    /**
     * @return string|null
     */
    public static function getJsonSchema(): ?string
    {
        return null;
    }

    /**
     * @param array $payload
     * @return self
     * @throws Exception
     */
    public static function fromPayload(array $payload): self
    {
        $self = new self();
        $self->id = $payload['id'];
        $self->packages = $payload['packages'];
        return $self;
    }

    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return Packages
     * @throws \JsonException
     */
    public function packages(): Packages
    {
        return Packages::fromArray($this->packages);
    }
}
