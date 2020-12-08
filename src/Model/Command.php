<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use Exception;

abstract class Command
{
    protected array $metadata = [];

    protected DateTimeImmutable $dateTime;

    abstract public static function fromPayload(array $payload);

    public static function getMessageName(): string
    {
        return str_replace('Command', '', lcfirst(substr(static::class, strrpos(static::class, '\\') + 1)));
    }

    public static function getJsonSchema(): ?string
    {
        return null;
    }

    /**
     * @throws Exception
     */
    protected function __construct()
    {
        $this->dateTime = new DateTimeImmutable('now');
    }

    public function withAddedMetadata(string $key, $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function getMetadataByKey(string $key)
    {
        return $this->metadata[$key] ?? null;
    }

    public function dateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}
