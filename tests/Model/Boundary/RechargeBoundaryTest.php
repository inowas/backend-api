<?php

declare(strict_types=1);

namespace App\Tests\Model\Boundary;

use App\Model\Modflow\Boundary\BoundaryFactory;
use App\Model\Modflow\Boundary\RechargeBoundary;
use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Swaggest\JsonSchema\Schema;

class RechargeBoundaryTest extends TestCase
{

    private array $rechargeBoundaryJson;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->rechargeBoundaryJson = [
            'id' => Uuid::uuid4()->toString(),
            'type' => "Feature",
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [[125.6, 10.1], [125.7, 10.1], [125.7, 10.2], [125.6, 10.2], [125.6, 10.1]]
                ]
            ],
            'properties' => [
                'type' => 'rch',
                'name' => 'My new Recharge',
                'layers' => [1],
                'cells' => [[3, 4], [4, 5]],
                'sp_values' => [[0.0002], [0.0002], [0.0002], [0.0002]]
            ]
        ];
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_validates_the_recharge_boundary_schema_successfully(): void
    {
        $schema = 'https://schema.inowas.com/modflow/boundary/rechargeBoundary.json';
        $schema = Schema::import($schema);
        $object = json_decode(json_encode($this->rechargeBoundaryJson, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);

        $wellBoundary = BoundaryFactory::fromArray($this->rechargeBoundaryJson);
        $object = json_decode(json_encode($wellBoundary->jsonSerialize(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_creates_a_recharge_boundary_from_json()
    {
        /** @var RechargeBoundary $rechargeBoundary */
        $rechargeBoundary = BoundaryFactory::fromArray($this->rechargeBoundaryJson);
        self::assertInstanceOf(RechargeBoundary::class, $rechargeBoundary);
        self::assertEquals($this->rechargeBoundaryJson['id'], $rechargeBoundary->getId());
        self::assertEquals($this->rechargeBoundaryJson['type'], $rechargeBoundary->getType());

        self::assertEquals($this->rechargeBoundaryJson['geometry']['type'], $rechargeBoundary->geometry()->getType());
        self::assertEquals($this->rechargeBoundaryJson['geometry']['coordinates'], $rechargeBoundary->geometry()->getCoordinates());
        self::assertEquals($this->rechargeBoundaryJson['properties'], $rechargeBoundary->getProperties());
        self::assertEquals($this->rechargeBoundaryJson['properties']['type'], $rechargeBoundary->type());
        self::assertEquals($this->rechargeBoundaryJson['properties']['layers'], $rechargeBoundary->layers());
        self::assertEquals($this->rechargeBoundaryJson['properties']['cells'], $rechargeBoundary->cells());
        self::assertEquals($this->rechargeBoundaryJson['properties']['sp_values'], $rechargeBoundary->spValues());
        self::assertEquals($this->rechargeBoundaryJson, $rechargeBoundary->jsonSerialize());
    }
}
