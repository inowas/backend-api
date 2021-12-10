<?php

namespace App\Model\Modflow;

use App\Model\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Throwable;

/**
 * @ORM\Entity()
 * @ORM\Table(name="modflow_model_packages")
 */
class Packages extends ValueObject
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string", unique=true, nullable=false)
     */
    private string $id;

    /**
     * @ORM\Column(name="data", type="text", nullable=false)
     */
    private string $jsonData = '[]';

    public function clone(string $id): self
    {
        $clone = clone $this;
        $clone->setId($id);
        return $clone;
    }

    public function __clone()
    {
        $this->id = Uuid::uuid4()->toString();
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param array $arr
     * @return static
     * @throws \JsonException
     */
    public static function fromArray(array $arr): self
    {
        $self = new self();
        $self->jsonData = json_encode($arr, JSON_THROW_ON_ERROR);
        return $self;
    }

    /**
     * @param string $str
     * @return static
     */
    public static function fromString(string $str): self
    {
        $self = new self();
        $self->jsonData = $str;
        return $self;
    }

    private function __construct()
    {
    }

    public function setJsonData(string $data): self
    {
        $this->jsonData = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        try {
            return json_decode($this->jsonData, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $ex) {
            return [];
        }
    }

    public function toString(): string
    {
        return $this->jsonData;
    }
}
