<?php

namespace App\Tests\Controller;

use App\Model\SimpleTool\SimpleTool;
use Ramsey\Uuid\Uuid;

class SimpleToolCommandsTest extends CommandTestBaseClass
{
    /**
     * @test
     * @throws \Exception
     */
    public function sendCreateSimpleToolCommand(): void
    {
        $user = $this->createRandomUser();
        $toolInstanceId = Uuid::uuid4()->toString();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'createToolInstance',
            'metadata' => (object)[],
            'payload' => [
                'id' => $toolInstanceId,
                'tool' => 'T0TEST',
                'name' => 'ToolName',
                'description' => 'ToolDescription',
                'public' => false,
                'data' => ['1234' => '5678']
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $simpleTool */
        $simpleTool = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $toolInstanceId]);
        self::assertEquals($command['payload']['tool'], $simpleTool->tool());
        self::assertEquals($command['payload']['name'], $simpleTool->name());
        self::assertEquals($command['payload']['description'], $simpleTool->description());
        self::assertEquals($command['payload']['public'], $simpleTool->isPublic());
        self::assertEquals($command['payload']['data'], $simpleTool->data());
        self::assertEquals($user->getId()->toString(), $simpleTool->userId());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function anAdminCanCreateToolsForOtherUsers(): void
    {
        $user = $this->createRandomUser();

        $adminUsername = 'admin' . random_int(1000000, 9999999);
        $adminPassword = 'password' . random_int(1000000, 9999999);
        $admin = $this->createUser($adminUsername, $adminPassword, ['ROLE_ADMIN']);

        $toolInstanceId = Uuid::uuid4()->toString();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'createToolInstance',
            'metadata' => ['user_id' => $user->getId()->toString()],
            'payload' => [
                'id' => $toolInstanceId,
                'tool' => 'T0TEST',
                'name' => 'ToolName',
                'description' => 'ToolDescription',
                'public' => false,
                'data' => ['1234' => '5678']
            ]
        ];

        $token = $this->getToken($admin->getUsername(), $admin->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $simpleTool */
        $simpleTool = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $toolInstanceId]);
        self::assertEquals($command['payload']['tool'], $simpleTool->tool());
        self::assertEquals($command['payload']['name'], $simpleTool->name());
        self::assertEquals($command['payload']['description'], $simpleTool->description());
        self::assertEquals($command['payload']['public'], $simpleTool->isPublic());
        self::assertEquals($command['payload']['data'], $simpleTool->data());
        self::assertEquals($user->getId()->toString(), $simpleTool->userId());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function aUserCanNotCreateToolsForOtherUsers(): void
    {
        $user = $this->createRandomUser();
        $toolInstanceId = Uuid::uuid4()->toString();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'createToolInstance',
            'metadata' => ['user_id' => Uuid::uuid4()->toString()],
            'payload' => [
                'id' => $toolInstanceId,
                'tool' => 'T0TEST',
                'name' => 'ToolName',
                'description' => 'ToolDescription',
                'public' => false,
                'data' => ['1234' => '5678']
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $simpleTool */
        $simpleTool = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $toolInstanceId]);
        self::assertEquals($command['payload']['tool'], $simpleTool->tool());
        self::assertEquals($command['payload']['name'], $simpleTool->name());
        self::assertEquals($command['payload']['description'], $simpleTool->description());
        self::assertEquals($command['payload']['public'], $simpleTool->isPublic());
        self::assertEquals($command['payload']['data'], $simpleTool->data());
        self::assertEquals($user->getId()->toString(), $simpleTool->userId());
    }

    /**
     * @test
     * @depends sendCreateSimpleToolCommand
     * @throws \Exception
     */
    public function sendCloneToolInstanceCommand(): void
    {
        $user = $this->createRandomUser();
        $simpleTool = $this->createSimpleTool($user);

        $user2 = $this->createRandomUser();

        $cloneId = Uuid::uuid4()->toString();
        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'cloneToolInstance',
            'metadata' => (object)[],
            'payload' => [
                'id' => $cloneId,
                'base_id' => $simpleTool->id()
            ]
        ];

        $token = $this->getToken($user2->getUsername(), $user2->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $clone */
        $clone = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $cloneId]);
        self::assertEquals($simpleTool->tool(), $clone->tool());
        self::assertEquals($simpleTool->name() . ' (clone)', $clone->name());
        self::assertEquals($simpleTool->description(), $clone->description());
        self::assertEquals($simpleTool->isPublic(), $clone->isPublic());
        self::assertEquals($simpleTool->data(), $clone->data());
        self::assertEquals($user2->getId()->toString(), $clone->userId());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendUpdateSimpleToolCommand(): void
    {
        $user = $this->createRandomUser();
        $simpleTool = $this->createSimpleTool($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateToolInstance',
            'metadata' => (object)[],
            'payload' => [
                'id' => $simpleTool->id(),
                'name' => 'ToolNewName',
                'description' => 'ToolNewDescription',
                'public' => true,
                'data' => ['a' => 'very', 'complex' => 'dataset']
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $simpleTool */
        $simpleTool = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $simpleTool->id()]);
        self::assertEquals($command['payload']['name'], $simpleTool->name());
        self::assertEquals($command['payload']['description'], $simpleTool->description());
        self::assertEquals($command['payload']['public'], $simpleTool->isPublic());
        self::assertEquals($command['payload']['data'], $simpleTool->data());
        self::assertEquals($user->getId()->toString(), $simpleTool->userId());
    }

    /**
     * @test
     * @depends sendCreateSimpleToolCommand
     * @throws \Exception
     */
    public function sendUpdateSimpleToolMetadataCommand(): void
    {
        $user = $this->createRandomUser();
        $simpleTool = $this->createSimpleTool($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateToolInstanceMetadata',
            'metadata' => (object)[],
            'payload' => [
                'id' => $simpleTool->id(),
                'name' => 'ToolNewNameUpdated',
                'description' => 'ToolNewDescriptionUpdated',
                'public' => true
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $simpleTool */
        $simpleTool = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $simpleTool->id()]);
        self::assertEquals($command['payload']['name'], $simpleTool->name());
        self::assertEquals($command['payload']['description'], $simpleTool->description());
        self::assertEquals($command['payload']['public'], $simpleTool->isPublic());
        self::assertEquals($user->getId()->toString(), $simpleTool->userId());
    }

    /**
     * @test
     * @depends sendCreateSimpleToolCommand
     * @throws \Exception
     */
    public function sendUpdateSimpleToolDataCommand(): void
    {
        $user = $this->createRandomUser();
        $simpleTool = $this->createSimpleTool($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'updateToolInstanceData',
            'metadata' => (object)[],
            'payload' => [
                'id' => $simpleTool->id(),
                'data' => ['a' => 'very', 'complex' => 'dataset', 'update' => 'now']
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $simpleTool */
        $simpleTool = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $simpleTool->id()]);
        self::assertEquals($command['payload']['data'], $simpleTool->data());
        self::assertEquals($user->getId()->toString(), $simpleTool->userId());
    }

    /**
     * @test
     * @throws \Exception
     */
    public function sendDeleteToolInstanceCommand(): void
    {
        $user = $this->createRandomUser();
        $simpleTool = $this->createSimpleTool($user);

        $command = [
            'uuid' => Uuid::uuid4()->toString(),
            'message_name' => 'deleteToolInstance',
            'metadata' => (object)[],
            'payload' => [
                'id' => $simpleTool->id()
            ]
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var SimpleTool $simpleTool */
        $simpleTool = $this->em->getRepository(SimpleTool::class)->findOneBy(['id' => $simpleTool->id()]);
        self::assertTrue($simpleTool->isArchived());
    }
}
