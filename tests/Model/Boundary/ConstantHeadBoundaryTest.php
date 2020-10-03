<?php

declare(strict_types=1);

namespace App\Tests\Model\Boundary;

use App\Model\Modflow\Boundary\BoundaryFactory;
use App\Model\Modflow\Boundary\ConstantHeadBoundary;
use Exception;
use GeoJson\Feature\Feature;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Swaggest\JsonSchema\Schema;

class ConstantHeadBoundaryTest extends TestCase
{

    private array $constantHeadBoundaryJson;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->constantHeadBoundaryJson = [
            'type' => "FeatureCollection",
            'features' => [
                [
                    'type' => 'Feature',
                    'id' => Uuid::uuid4()->toString(),
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => [[125.6, 10.1], [125.7, 10.2], [125.8, 10.3]]
                    ],
                    'properties' => [
                        'type' => 'chd',
                        'name' => 'My new chd-boundary',
                        'layers' => [1],
                        'cells' => [[3, 4], [4, 5]],
                    ]
                ],
                [
                    'type' => 'Feature',
                    'id' => 'op1',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [125.6, 10.1]
                    ],
                    'properties' => [
                        'type' => 'op',
                        'name' => 'OP1',
                        'sp_values' => [
                            [1, 2]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_validates_the_constant_head_boundary_schema_successfully(): void
    {
        $schema = 'https://schema.inowas.com/modflow/boundary/constantHeadBoundary.json';
        $schema = Schema::import($schema);
        $object = json_decode(json_encode($this->constantHeadBoundaryJson, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);

        $constantHeadBoundary = BoundaryFactory::fromArray($this->constantHeadBoundaryJson);
        $object = json_decode(json_encode($constantHeadBoundary->jsonSerialize(), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        $schema->in($object);
        self::assertTrue(true);
    }

    /**
     * @test
     * @throws Exception
     */
    public function it_creates_a_constant_head_boundary_from_json(): void
    {
        /** @var ConstantHeadBoundary $constantHeadBoundary */
        $constantHeadBoundary = BoundaryFactory::fromArray($this->constantHeadBoundaryJson);
        self::assertInstanceOf(ConstantHeadBoundary::class, $constantHeadBoundary);
        self::assertInstanceOf(Feature::class, $constantHeadBoundary->constantHeadBoundary());
        self::assertCount(1, $constantHeadBoundary->observationPoints());
        self::assertEquals($this->constantHeadBoundaryJson, $constantHeadBoundary->jsonSerialize());
    }
}
