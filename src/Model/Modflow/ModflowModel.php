<?php

declare(strict_types=1);

namespace App\Model\Modflow;

use App\Model\Modflow\Boundary\BoundaryCollection;
use App\Model\ToolInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ModflowModelRepository")
 */
class ModflowModel extends ToolInstance
{

    /**
     * @ORM\Column(name="discretization", type="json", nullable=false)
     */
    private array $discretization = [];

    /**
     * @ORM\Column(name="soilmodel", type="json", nullable=false)
     */
    private array $soilmodel = [];

    /**
     * @ORM\Column(name="boundaries", type="json", nullable=false)
     */
    private array $boundaries = [];

    /**
     * @ORM\Column(name="transport", type="json", nullable=true)
     */
    private ?array $transport = [];

    /**
     * @ORM\Column(name="variable_density", type="json", nullable=true)
     */
    private ?array $variableDensity = [];

    /**
     * @ORM\Column(name="calculation", type="json", nullable=false)
     */
    private array $calculation = [];

    /**
     * @ORM\OneToOne(targetEntity="Packages", mappedBy="packages", cascade={"all"})
     */
    private Packages $packages;

    public static function create(): ModflowModel
    {
        return new self();
    }

    public static function fromArray(array $arr): ModflowModel
    {
        $self = new self();
        $self->discretization = $arr['discretization'] ?? [];
        $self->soilmodel = $arr['soilmodel'] ?? [];
        $self->boundaries = $arr['boundaries'] ?? [];
        $self->transport = $arr['transport'] ?? [];
        $self->variableDensity = $arr['variableDensity'] ?? [];
        $self->calculation = $arr['calculation'] ?? [];
        $self->packages = $arr['packages'] ? Packages::fromArray($arr['packages']) : Packages::fromArray([]);
        return $self;
    }

    public function discretization(): Discretization
    {
        return Discretization::fromArray($this->discretization);
    }

    public function setDiscretization(Discretization $discretization): void
    {
        $this->discretization = $discretization->toArray();
    }

    public function boundaries(): BoundaryCollection
    {
        return BoundaryCollection::fromArray($this->boundaries);
    }

    public function setBoundaries(BoundaryCollection $boundaries): void
    {
        $this->boundaries = $boundaries->toArray();
    }

    public function transport(): Transport
    {
        if (null === $this->transport) {
            $this->transport = [];
        }
        return Transport::fromArray($this->transport);
    }

    public function setTransport(Transport $transport): void
    {
        $this->transport = $transport->toArray();
    }

    public function variableDensity(): VariableDensity
    {
        if (null === $this->variableDensity) {
            $this->variableDensity = [];
        }
        return VariableDensity::fromArray($this->variableDensity);
    }

    public function setVariableDensity(VariableDensity $variableDensity): void
    {
        $this->variableDensity = $variableDensity->toArray();
    }

    public function calculation(): Calculation
    {
        return Calculation::fromArray($this->calculation);
    }

    public function setCalculation(Calculation $calculation): void
    {
        $this->calculation = $calculation->toArray();
    }

    public function packages(): Packages
    {
        return $this->packages;
    }

    public function setPackages(Packages $packages): void
    {
        $this->packages = $packages;
    }

    public function soilmodel(): Soilmodel
    {
        return Soilmodel::fromArray($this->soilmodel);
    }

    public function setSoilmodel(Soilmodel $soilmodel): void
    {
        $this->soilmodel = $soilmodel->toArray();
    }

    public function data(): array
    {
        return ['discretization' => $this->discretization];
    }

    public function setData(array $data): void
    {
        $this->setDiscretization(Discretization::fromArray($data));
    }

    public function toArray(): array
    {
        return [
            'discretization' => $this->discretization,
            'soilmodel' => $this->soilmodel,
            'boundaries' => $this->boundaries,
            'calculation' => $this->calculation,
            'packages' => $this->packages->toArray(),
        ];
    }
}
