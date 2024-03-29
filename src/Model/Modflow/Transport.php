<?php

namespace App\Model\Modflow;

use App\Model\ValueObject;

final class Transport extends ValueObject
{
    private array $data;

    public static function fromArray(array $arr): self
    {
        $self = new self();
        $self->data = $arr;
        return $self;
    }

    private function __construct()
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
