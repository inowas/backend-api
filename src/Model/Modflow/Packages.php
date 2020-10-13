<?php

namespace App\Model\Modflow;

use App\Model\ValueObject;

final class Packages extends ValueObject
{
    private string $json = '[]';

    /**
     * @param array $arr
     * @return static
     * @throws \JsonException
     */
    public static function fromArray(array $arr): self
    {
        $self = new self();
        $self->json = json_encode($arr, JSON_THROW_ON_ERROR);
        return $self;
    }

    /**
     * @param string $str
     * @return static
     */
    public static function fromString(string $str): self
    {
        $self = new self();
        $self->json = $str;
        return $self;
    }

    private function __construct()
    {
    }

    /**
     * @return array
     * @throws \JsonException
     */
    public function toArray(): array
    {
        return json_decode($this->json, true, 512, JSON_THROW_ON_ERROR);
    }

    public function toString(): string
    {
        return $this->json;
    }
}
