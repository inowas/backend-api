<?php

namespace App\Tests\Model\Modflow;

use App\Model\Modflow\Discretization;
use PHPUnit\Framework\TestCase;

class DiscretizationTest extends TestCase
{
    private array $geometry;
    private array $boundingBox;
    private array $gridSize;
    private array $cells;
    private array $stressperiods;
    private int $timeUnit;
    private int $lengthUnit;
    private float $rotation;
    private float $intersection;

    public function setUp(): void
    {
        $this->geometry = [
            'type' => 'Polygon',
            'coordinates' => [[5, 5], [5, 6], [5, 7], [5, 5]]
        ];

        $this->boundingBox = [
            [1, 1], [10, 10]
        ];

        $this->gridSize = [
            'n_x' => 10,
            'n_y' => 20
        ];

        $this->cells = [
            [5, 6],
            [5, 7],
            [5, 8],
            [5, 9],
            [6, 6],
            [6, 7],
            [6, 8],
        ];

        $this->stressperiods = [
            'start_date_time' => '2010-01-01',
            'end_date_time' => '2019-12-31',
            'time_unit' => 4,
            'stressperiods' => [
                ['totim_start' => 0, 'perlen' => 31, 'nstp' => 1, 'tsmult' => 1, 'steady' => true],
                ['totim_start' => 31, 'perlen' => 31, 'nstp' => 1, 'tsmult' => 1, 'steady' => false],
                ['totim_start' => 62, 'perlen' => 31, 'nstp' => 1, 'tsmult' => 1, 'steady' => false]
            ]
        ];

        $this->timeUnit = 4;
        $this->lengthUnit = 1;

        $this->rotation = -5.0;
        $this->intersection = 0.25;
    }

    /**
     * @test
     */
    public function can_be_instantiated_from_params(): void
    {
        $disc = Discretization::fromParams($this->geometry, $this->boundingBox, $this->gridSize, $this->cells, $this->stressperiods, $this->lengthUnit, $this->timeUnit, $this->intersection, $this->rotation);
        self::assertInstanceOf(Discretization::class, $disc);
        self::assertEquals($this->geometry, $disc->geometry());
        self::assertEquals($this->boundingBox, $disc->boundingBox());
        self::assertEquals($this->gridSize, $disc->gridSize());
        self::assertEquals($this->cells, $disc->cells());
        self::assertEquals($this->stressperiods, $disc->stressperiods());
        self::assertEquals($this->timeUnit, $disc->timeUnit());
        self::assertEquals($this->lengthUnit, $disc->lengthUnit());
        self::assertEquals($this->rotation, $disc->rotation());
        self::assertEquals($this->intersection, $disc->intersection());
    }

    /**
     * @test
     */
    public function can_be_instantiated_from_array_and_converted_to_array(): void
    {

        $arr = [
            'geometry' => $this->geometry,
            'bounding_box' => $this->boundingBox,
            'grid_size' => $this->gridSize,
            'cells' => $this->cells,
            'stressperiods' => $this->stressperiods,
            'length_unit' => $this->lengthUnit,
            'time_unit' => $this->timeUnit,
            'intersection' => $this->intersection,
            'rotation' => $this->rotation,
        ];

        $disc = Discretization::fromArray($arr);
        self::assertEquals($arr, $disc->toArray());
    }

    /**
     * @test
     */
    public function can_be_checked_if_equals_to_other_instance(): void
    {

        $arr1 = $arr2 = [
            'geometry' => $this->geometry,
            'bounding_box' => $this->boundingBox,
            'grid_size' => $this->gridSize,
            'cells' => $this->cells,
            'stressperiods' => $this->stressperiods,
            'length_unit' => $this->lengthUnit,
            'time_unit' => $this->timeUnit,
            'intersection' => $this->intersection,
            'rotation' => $this->rotation,
        ];

        $disc = Discretization::fromArray($arr1);
        self::assertTrue($disc->isEqualTo(Discretization::fromArray($arr2)));

        $arr2['time_unit'] = 3;
        self::assertFalse($disc->isEqualTo(Discretization::fromArray($arr2)));

        $arr2 = $arr1;
        $arr2['stressperiods']['stressperiods']['totim_start'] = 1;
        self::assertFalse($disc->isEqualTo(Discretization::fromArray($arr2)));
    }

    /**
     * @test
     */
    public function it_can_calculate_a_diff_to_another_instance(): void
    {
        $arr1 = $arr2 = [
            'geometry' => $this->geometry,
            'bounding_box' => $this->boundingBox,
            'grid_size' => $this->gridSize,
            'cells' => $this->cells,
            'stressperiods' => $this->stressperiods,
            'length_unit' => $this->lengthUnit,
            'time_unit' => $this->timeUnit,
            'intersection' => $this->intersection,
            'rotation' => $this->rotation,
        ];

        $arr2['geometry']['coordinates'] = [[5, 5], [5, 6], [5, 7], [5, 6]];
        self::assertEquals(['geometry' => ['coordinates' => ["3" => ["1" => 6]]]], Discretization::fromArray($arr1)->diff(Discretization::fromArray($arr2)));

        $arr2 = $arr1;
        $arr2['stressperiods']['stressperiods']['totim_start'] = 1;
        self::assertEquals(['stressperiods' => ['stressperiods' => ['totim_start' => 1]]], Discretization::fromArray($arr1)->diff(Discretization::fromArray($arr2)));
    }

    /**
     * @test
     */
    public function it_can_merge_a_diff_and_create_a_new_instance(): void
    {
        $arr1 = $arr2 = [
            'geometry' => $this->geometry,
            'bounding_box' => $this->boundingBox,
            'grid_size' => $this->gridSize,
            'cells' => $this->cells,
            'stressperiods' => $this->stressperiods,
            'length_unit' => $this->lengthUnit,
            'time_unit' => $this->timeUnit,
            'intersection' => $this->intersection,
            'rotation' => $this->rotation,
        ];

        $arr2['geometry']['coordinates'] = [[5, 5], [5, 6], [5, 7], [5, 6]];

        $disc = Discretization::fromArray($arr1);
        $diff = $disc->diff(Discretization::fromArray($arr2));
        self::assertEquals($arr2, $disc->merge($diff)->toArray());
    }

    /**
     * @test
     */
    public function it_can_create_and_merge_a_shallow_diff(): void
    {
        $arr1 = $arr2 = [
            'geometry' => $this->geometry,
            'bounding_box' => $this->boundingBox,
            'grid_size' => $this->gridSize,
            'cells' => $this->cells,
            'stressperiods' => $this->stressperiods,
            'length_unit' => $this->lengthUnit,
            'time_unit' => $this->timeUnit,
            'intersection' => $this->intersection,
            'rotation' => $this->rotation,
        ];

        $arr2['geometry']['coordinates'] = [[5, 5], [5, 6], [5, 7], [5, 6]];

        $expected = ['geometry' => $arr2['geometry']];
        $disc = Discretization::fromArray($arr1);
        $diff = $disc->array_shallow_diff(Discretization::fromArray($arr2));
        self::assertEquals($expected, $diff);

        /** @var Discretization $disc * */
        $disc = $disc->array_merge_shallow_diff($diff);
        self::assertEquals($arr2['geometry'], $disc->geometry());
    }
}
