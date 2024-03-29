<?php

namespace App\Tests\Controller;

use App\Model\Modflow\Boundary\BoundaryCollection;
use App\Model\Modflow\Boundary\BoundaryFactory;
use App\Model\Modflow\Discretization;
use App\Model\Modflow\Layer;
use App\Model\Modflow\ModflowModel;
use App\Model\Modflow\Packages;
use App\Model\Modflow\Soilmodel;
use App\Model\ToolMetadata;
use App\Model\User;
use Ramsey\Uuid\Uuid;

class ModflowModelCommandsTest extends CommandTestBaseClass
{
    /**
     * @test
     * @throws \Exception
     */
    public function sendCreateModflowModelCommand(): void
    {
        $user = $this->createRandomUser();
        $modelId = Uuid::uuid4()->toString();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'createModflowModel',
            'metadata' => (object)[],
            'payload' => [
                'id' => $modelId,
                'name' => 'New numerical groundwater model',
                'description' => 'This is the model description',
                'public' => true,
                'cells' => [[0, 1], [1, 1], [0, 0], [1, 0]],
                'bounding_box' => [[13.785759, 51.133180], [13.788094, 51.134608]],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [13.785759, 51.134162],
                        [13.786697, 51.134608],
                        [13.788094, 51.133921],
                        [13.786680, 51.133180],
                        [13.785759, 51.134162]
                    ]]
                ],
                'grid_size' => [
                    'n_x' => 2,
                    'n_y' => 2,
                ],
                'length_unit' => 2,
                'stressperiods' => [
                    'start_date_time' => '2000-01-01T00:00:00.000Z',
                    'end_date_time' => '2019-12-31T00:00:00.000Z',
                    'stressperiods' => [[
                        'totim_start' => 0,
                        'perlen' => 0,
                        'nstp' => 1,
                        'tsmult' => 1,
                        'steady' => true
                    ]],
                    'time_unit' => 4,
                ],
                'time_unit' => 4,
                'intersection' => 0.1,
                'rotation' => 12.5
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = $this->em->getRepository(ModflowModel::class)->findOneBy(['id' => $modelId]);
        self::assertInstanceOf(ModflowModel::class, $modflowModel);
        self::assertEquals('T03', $modflowModel->tool());
        self::assertEquals($command['payload']['name'], $modflowModel->name());
        self::assertEquals($command['payload']['description'], $modflowModel->description());
        self::assertEquals($command['payload']['public'], $modflowModel->isPublic());
        self::assertEquals($user->getId()->toString(), $modflowModel->userId());

        self::assertInstanceOf(Discretization::class, $modflowModel->discretization());
        $expected = Discretization::fromParams(
            $command['payload']['geometry'],
            $command['payload']['bounding_box'],
            $command['payload']['grid_size'],
            $command['payload']['cells'],
            $command['payload']['stressperiods'],
            $command['payload']['length_unit'],
            $command['payload']['time_unit'],
            $command['payload']['intersection'],
            $command['payload']['rotation'],
        );
        self::assertEquals($expected, $modflowModel->discretization());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUpdateModflowModelMetadataCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateModflowModelMetadata',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'name' => 'New numerical groundwater model - updated',
                'description' => 'This is the model description - updated',
                'public' => false
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertInstanceOf(ModflowModel::class, $modflowModel);

        self::assertEquals('T03', $modflowModel->tool());
        self::assertEquals($command['payload']['name'], $modflowModel->name());
        self::assertEquals($command['payload']['description'], $modflowModel->description());
        self::assertEquals($command['payload']['public'], $modflowModel->isPublic());
        self::assertEquals($user->getId()->toString(), $modflowModel->getUser()->getId()->toString());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUpdateModflowModelDiscretizationCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateModflowModelDiscretization',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'cells' => [[0, 1], [1, 1], [0, 0], [1, 0], [10, 10]],
                'bounding_box' => [[13, 51], [14, 52]],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [13, 51],
                        [13.786697, 51.134608],
                        [13.788094, 51.133921],
                        [13.786680, 51.133180],
                        [13, 51]
                    ]]
                ],
                'grid_size' => [
                    'n_x' => 2,
                    'n_y' => 4,
                ],
                'length_unit' => 3,
                'stressperiods' => [
                    'start_date_time' => '2000-01-02T00:00:00.000Z',
                    'end_date_time' => '2019-12-30T00:00:00.000Z',
                    'stressperiods' => [[
                        'totim_start' => 1,
                        'perlen' => 1,
                        'nstp' => 2,
                        'tsmult' => 2,
                        'steady' => false
                    ]],
                    'time_unit' => 3,
                ],
                'time_unit' => 3,
                'intersection' => 0.2,
                'rotation' => 25.5
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertInstanceOf(ModflowModel::class, $modflowModel);

        self::assertEquals('T03', $modflowModel->tool());
        self::assertEquals($user->getId()->toString(), $modflowModel->getUser()->getId()->toString());
        self::assertInstanceOf(Discretization::class, $modflowModel->discretization());
        $expected = Discretization::fromParams(
            $command['payload']['geometry'],
            $command['payload']['bounding_box'],
            $command['payload']['grid_size'],
            $command['payload']['cells'],
            $command['payload']['stressperiods'],
            $command['payload']['length_unit'],
            $command['payload']['time_unit'],
            $command['payload']['intersection'],
            $command['payload']['rotation']
        );
        self::assertEquals($expected, $modflowModel->discretization());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUpdateModflowModelStressperiodsCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateStressperiods',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'stressperiods' => [
                    'start_date_time' => '2000-01-03T00:00:00.000Z',
                    'end_date_time' => '2019-12-29T00:00:00.000Z',
                    'stressperiods' => [[
                        'totim_start' => 2,
                        'perlen' => 3,
                        'nstp' => 4,
                        'tsmult' => 4,
                        'steady' => true
                    ]],
                    'time_unit' => 3,
                ]
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertInstanceOf(ModflowModel::class, $modflowModel);
        self::assertInstanceOf(Discretization::class, $modflowModel->discretization());
        self::assertEquals($command['payload']['stressperiods'], $modflowModel->discretization()->stressperiods());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendCloneModflowModelAsToolCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $cloneId = Uuid::uuid4()->toString();

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'cloneModflowModel',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'new_id' => $cloneId,
                'is_tool' => true
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $original */
        $original = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());

        /** @var ModflowModel $clone */
        $clone = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($cloneId);

        self::assertEquals($clone->toArray(), $original->toArray());
        self::assertFalse($clone->isScenario());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendCloneModflowModelAsScenarioCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $cloneId = Uuid::uuid4()->toString();

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'cloneModflowModel',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'new_id' => $cloneId,
                'is_tool' => false
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $original */
        $original = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());

        /** @var ModflowModel $clone */
        $clone = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($cloneId);

        self::assertEquals($clone->toArray(), $original->toArray());
        self::assertTrue($clone->isScenario());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendDeleteModflowModelCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);
        self::assertFalse($model->isArchived());

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'deleteModflowModel',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertTrue($modflowModel->isArchived());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendAddBoundaryCommand(): void
    {
        $user = $this->createRandomUser();
        $modelId = $this->createRandomModflowModel($user)->id();

        $boundaryId = Uuid::uuid4()->toString();

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'addBoundary',
            'metadata' => (object)[],
            'payload' => [
                'id' => $modelId,
                'boundary' => [
                    'id' => $boundaryId,
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [12, 51]
                    ],
                    'properties' => [
                        'type' => 'wel',
                        'name' => 'My Well',
                        'well_type' => 'puw',
                        'layers' => [0],
                        'cells' => [[3, 2], [4, 2]],
                        'sp_values' => [[3444], [3444], [3444], [3444]]
                    ]
                ],
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($modelId);
        self::assertEquals($command['payload']['boundary'], $modflowModel->boundaries()->findById($boundaryId)->toArray());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUpdateBoundaryCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $boundary = $model->boundaries()->first();

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateBoundary',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'boundary_id' => $boundary->id(),
                'boundary' => [
                    'id' => $boundary->id(),
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [12, 51]
                    ],
                    'properties' => [
                        'type' => 'wel',
                        'name' => 'My Well',
                        'well_type' => 'puw',
                        'layers' => [0],
                        'cells' => [[3, 2], [4, 2]],
                        'sp_values' => [[3445], [3544], [3144], [3434]]
                    ]
                ]
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        $expected = [$command['payload']['boundary']['id'] => $command['payload']['boundary']];
        self::assertEquals($expected, $modflowModel->boundaries()->toArray());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendRemoveBoundaryCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $boundaryId = $model->boundaries()->first()->id();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'removeBoundary',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'boundary_id' => $boundaryId,
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertCount(0, $modflowModel->boundaries()->toArray());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendAddLayerCommand(): void
    {
        $user = $this->createRandomUser();
        $modelId = $this->createRandomModflowModel($user)->id();

        $layerId = Uuid::uuid4()->toString();

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'addLayer',
            'metadata' => (object)[],
            'payload' => [
                'id' => $modelId,
                'layer' => [
                    'id' => $layerId,
                    'name' => 'Added layer',
                    'description' => 'Added layer description',
                    'number' => 2,
                    'top' => 10,
                    'botm' => -10,
                    'hk' => 100,
                    'hani' => 2,
                    'vka' => 10,
                    'layavg' => 2,
                    'laytyp' => 2,
                    'laywet' => 2,
                    'ss' => 0.3,
                    'sy' => 0.3
                ],
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($modelId);
        self::assertEquals($command['payload']['layer'], $modflowModel->soilmodel()->findLayer($layerId)->toArray());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUpdateLayerCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $layerId = $model->soilmodel()->firstLayer()->id();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateLayer',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'layer' => [
                    'id' => $layerId,
                    'name' => 'Updated layer',
                    'description' => 'Updated layer description',
                    'number' => 3,
                    'top' => 11,
                    'botm' => -11,
                    'hk' => 101,
                    'hani' => 3,
                    'vka' => 11,
                    'layavg' => 12,
                    'laytyp' => 12,
                    'laywet' => 12,
                    'ss' => 0.23,
                    'sy' => 0.23
                ],
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertEquals($command['payload']['layer'], $modflowModel->soilmodel()->findLayer($layerId)->toArray());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendCloneLayerCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $layerId = $model->soilmodel()->firstLayer()->id();
        $newLayerId = Uuid::uuid4()->toString();

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'cloneLayer',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'layer_id' => $layerId,
                'new_layer_id' => $newLayerId
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertCount(2, $modflowModel->soilmodel()->layers());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function sendRemoveLayerCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $layerId = $model->soilmodel()->firstLayer()->id();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'removeLayer',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'layer_id' => $layerId
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertNull($modflowModel->soilmodel()->findLayer($layerId));
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUpdateSoilmodelPropertiesCommand(): void
    {
        $user = $this->createRandomUser();
        $model = $this->createRandomModflowModel($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateSoilmodelProperties',
            'metadata' => (object)[],
            'payload' => [
                'id' => $model->id(),
                'properties' => ['the' => 'new', 'properties' => 1, 2, 3]
            ],
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var ModflowModel $modflowModel */
        $modflowModel = self::$container->get('doctrine')->getRepository(ModflowModel::class)->findOneById($model->id());
        self::assertEquals($command['payload']['properties'], $modflowModel->soilmodel()->properties());
    }

    /**
     * @param User $user
     * @return ModflowModel
     * @throws \Exception
     */
    private function createRandomModflowModel(User $user): ModflowModel
    {
        $em = $this->em;
        $modelId = Uuid::uuid4()->toString();
        $modflowModel = ModflowModel::createWithParams(
            $modelId,
            $user,
            'T03',
            ToolMetadata::fromParams(
                sprintf('Model-Name %d', random_int(1000000, 10000000 - 1)),
                sprintf('Model-Description %d', random_int(1000000, 10000000 - 1)),
                true
            )
        );

        # Discretization
        $discretization = Discretization::fromArray([
            'cells' => [[0, 1], [1, 1], [0, 0], [1, 0]],
            'bounding_box' => [[13.785759, 51.133180], [13.788094, 51.134608]],
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [13.785759, 51.134162],
                    [13.786697, 51.134608],
                    [13.788094, 51.133921],
                    [13.786680, 51.133180],
                    [13.785759, 51.134162]
                ]]
            ],
            'grid_size' => [
                'n_x' => 2,
                'n_y' => 2,
            ],
            'length_unit' => 2,
            'stressperiods' => [
                'start_date_time' => '2000-01-01T00:00:00.000Z',
                'end_date_time' => '2019-12-31T00:00:00.000Z',
                'stressperiods' => [[
                    'totim_start' => 0,
                    'perlen' => 0,
                    'nstp' => 1,
                    'tsmult' => 1,
                    'steady' => true
                ]],
                'time_unit' => 4,
            ],
            'time_unit' => 4,
        ]);
        $modflowModel->setDiscretization($discretization);

        # BoundaryCollection
        $boundary = BoundaryFactory::fromArray([
            'id' => Uuid::uuid4()->toString(),
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [13, 52]
            ],
            'properties' => [
                'type' => 'wel',
                'name' => 'My new Well',
                'well_type' => 'puw',
                'layers' => [1],
                'cells' => [[3, 4], [4, 5]],
                'sp_values' => [3444, 5555, 666, 777]
            ]
        ]);
        $boundaries = BoundaryCollection::create();
        $boundaries->addBoundary($boundary);
        $modflowModel->setBoundaries($boundaries);

        # Soilmodel
        $soilmodel = Soilmodel::create();
        $layer = Layer::fromArray([
            'id' => Uuid::uuid4()->toString(),
            'name' => 'Default layer',
            'description' => 'Default layer description',
            'number' => 1,
            'top' => 0,
            'botm' => -100,
            'hk' => 200,
            'hani' => 1,
            'vka' => 20,
            'layavg' => 1,
            'laytyp' => 1,
            'laywet' => 1,
            'ss' => 0.2,
            'sy' => 0.2
        ]);
        $soilmodel->addLayer($layer);
        $modflowModel->setSoilmodel($soilmodel);

        $packages = Packages::fromString('')->setId($modelId);
        $em->persist($modflowModel);
        $em->persist($packages);
        $em->flush();

        return $modflowModel;
    }
}
