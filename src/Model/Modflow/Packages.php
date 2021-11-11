<?php

namespace App\Model\Modflow;

use App\Model\ValueObject;
use Doctrine\ORM\Mapping as ORM;
use Throwable;

/**
 * @ORM\Entity()
 * @ORM\Table(name="modflow_model_packages")
 */
class Packages extends ValueObject
{

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="ModflowModel")
     * @ORM\Column(name="id")
     */
    private ModflowModel $modflowModel;

    /**
     * @ORM\Column(name="data", type="text", nullable=false)
     */
    private string $jsonData = '[]';

    /**
     * @param ModflowModel $modflowModel
     */
    public function setModflowModel(ModflowModel $modflowModel): void
    {
        $this->modflowModel = $modflowModel;
    }

    /**
     * @return ModflowModel
     */
    public function getModflowModel(): ModflowModel
    {
        return $this->modflowModel;
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

    /**
     * @return array
     * @throws \JsonException
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
