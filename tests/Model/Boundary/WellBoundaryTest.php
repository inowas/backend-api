<?php

declare(strict_types=1);

namespace App\Tests\Model\Boundary;

use App\Model\Modflow\Boundary\BoundaryFactory;
use App\Model\Modflow\Boundary\WellBoundary;
use Exception;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Swaggest\JsonSchema\Schema;

class WellBoundaryTest extends TestCase
{

    private array $wellBoundaryJson;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->wellBoundaryJson = [
            'id' => Uuid::uuid4()->toString(),
            'type' => "Feature",
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [125.6, 10.1]
            ],
            'properties' => [
                'type' => 'wel',
                'name' => 'My new Well',
                'well_type' => 'puw',
                'layers' => [1],
                'cells' => [[3, 4], [4, 5]],
                'sp_values' => [[3444], [3445], [3446], [3447]]
            ]
        ];
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_validates_the_well_boundary_schema_successfully(): void
    {
        $schema = __DIR__.'/../../../schema/modflow/boundary/wellBoundary.json';
        $schema = Schema::import($schema);
        $object = json_decode(json_encode($this->wellBoundaryJson, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);

        $wellBoundary = BoundaryFactory::fromArray($this->wellBoundaryJson);
        $object = json_decode(json_encode($wellBoundary->jsonSerialize(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_creates_a_well_from_json()
    {
        /** @var WellBoundary $wellBoundary */
        $wellBoundary = BoundaryFactory::fromArray($this->wellBoundaryJson);
        self::assertInstanceOf(WellBoundary::class, $wellBoundary);
        self::assertEquals($this->wellBoundaryJson['id'], $wellBoundary->getId());
        self::assertEquals($this->wellBoundaryJson['type'], $wellBoundary->getType());

        self::assertEquals($this->wellBoundaryJson['geometry']['type'], $wellBoundary->geometry()->getType());
        self::assertEquals($this->wellBoundaryJson['geometry']['coordinates'], $wellBoundary->geometry()->getCoordinates());
        self::assertEquals($this->wellBoundaryJson['properties'], $wellBoundary->getProperties());
        self::assertEquals($this->wellBoundaryJson['properties']['type'], $wellBoundary->type());
        self::assertEquals($this->wellBoundaryJson['properties']['well_type'], $wellBoundary->wellType());
        self::assertEquals($this->wellBoundaryJson['properties']['layers'], $wellBoundary->layers());
        self::assertEquals($this->wellBoundaryJson['properties']['cells'], $wellBoundary->cells());
        self::assertEquals($this->wellBoundaryJson['properties']['sp_values'], $wellBoundary->spValues());
        self::assertEquals($this->wellBoundaryJson, $wellBoundary->jsonSerialize());
    }

}
