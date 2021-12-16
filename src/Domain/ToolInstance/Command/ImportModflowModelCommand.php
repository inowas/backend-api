<?php

declare(strict_types=1);

namespace App\Domain\ToolInstance\Command;

use App\Model\Command;
use App\Model\Modflow\Boundary\BoundaryCollection;
use App\Model\Modflow\Boundary\BoundaryFactory;
use App\Model\Modflow\Calculation;
use App\Model\Modflow\Discretization;
use App\Model\Modflow\Layer;
use App\Model\Modflow\Packages;
use App\Model\Modflow\Soilmodel;
use App\Model\Modflow\Transport;
use App\Model\Modflow\VariableDensity;
use Assert\AssertionFailedException;
use Exception;
use JsonException;

class ImportModflowModelCommand extends Command
{
    private string $id;
    private string $name;
    private string $description;
    private bool $isPublic;
    private array $discretization;
    private array $soilmodel;
    private array $boundaries;
    private array $transport;
    private array $variableDensity;
    private array $calculation;
    private array $packages;


    /**
     * @return string|null
     */
    public static function getJsonSchema(): ?string
    {
        return sprintf('%s%s', __DIR__, '/../../../../schema/commands/importModflowModel.json');
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
        $self->name = $payload['name'];
        $self->description = $payload['description'];
        $self->isPublic = $payload['public'];
        $self->discretization = $payload['discretization'];
        $self->soilmodel = $payload['soilmodel'];
        $self->boundaries = $payload['boundaries'];
        $self->transport = $payload['transport'] ?? [];
        $self->variableDensity = $payload['variableDensity'] ?? [];
        $self->calculation = $payload['calculation'] ?? [];
        $self->packages = $payload['packages'] ?? [];
        return $self;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function discretization(): Discretization
    {
        return Discretization::fromArray($this->discretization);
    }

    public function soilmodel(): Soilmodel
    {
        $soilmodel = Soilmodel::create();
        foreach ($this->soilmodel['layers'] as $layer) {
            $soilmodel->addLayer(Layer::fromArray($layer));
        }

        if (array_key_exists('properties', $this->soilmodel)) {
            $soilmodel->updateProperties($this->soilmodel['properties']);
        }

        return $soilmodel;
    }

    /**
     * @throws Exception|AssertionFailedException
     */
    public function boundaries(): BoundaryCollection
    {
        $boundaries = BoundaryCollection::create();
        foreach ($this->boundaries as $boundary) {
            $boundaries->addBoundary(BoundaryFactory::fromArray($boundary));
        }

        return $boundaries;
    }

    public function transport(): Transport
    {
        return Transport::fromArray($this->transport);
    }

    public function variableDensity(): VariableDensity
    {
        return VariableDensity::fromArray($this->variableDensity);
    }

    public function calculation(): Calculation
    {
        return Calculation::fromArray($this->calculation);
    }

    /**
     * @throws JsonException
     */
    public function packages(): Packages
    {
        return Packages::fromArray($this->packages);
    }
}
