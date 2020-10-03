<?php

namespace App\Tests\Controller;

use App\Model\User;

class UserCommandsTest extends CommandTestBaseClass
{

    /**
     * @test
     * @throws \Exception
     */
    public function aUserCanRegister(): array
    {
        $name = sprintf('newUser_%d', random_int(1000000, 10000000 - 1));
        $email = $name . '@inowas.com';
        $password = sprintf('newUserPassword_%d', random_int(1000000, 10000000 - 1));

        $client = $this->client;
        $client->request(
            'POST',
            '/v3/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => $name,
                'email' => $email,
                'password' => $password
            ], JSON_THROW_ON_ERROR)
        );

        self::assertEquals(202, $client->getResponse()->getStatusCode());

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($email);
        self::assertInstanceOf(User::class, $user);
        self::assertEquals(['ROLE_USER'], $user->getRoles());
        return ['username' => $user->getUsername(), 'password' => $password];
    }

    /**
     * @test
     * @depends aUserCanRegister
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function aUserCanChangeUsername(array $credentials): array
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        $newUserName = sprintf('newUser_%d', random_int(1000000, 10000000 - 1));
        $command = [
            'message_name' => 'changeUsername',
            'payload' => [
                'username' => $newUserName
            ]
        ];
        $token = $this->getToken($username, $password);
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($newUserName);
        self::assertInstanceOf(User::class, $user);
        self::assertEquals($newUserName, $user->getUsername());

        return ['username' => $newUserName, 'password' => $password];
    }

    /**
     * @test
     * @depends aUserCanChangeUsername
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function aUserCanChangePassword(array $credentials): array
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        $newPassword = sprintf('newPassword_%d', random_int(1000000, 10000000 - 1));
        $command = [
            'message_name' => 'changeUserPassword',
            'payload' => [
                'password' => $password,
                'new_password' => $newPassword
            ]
        ];

        $token = $this->getToken($username, $password);
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);
        self::assertInstanceOf(User::class, $user);
        self::assertEquals($newPassword, $user->getPassword());

        return ['username' => $username, 'password' => $newPassword];
    }

    /**
     * @test
     * @depends aUserCanChangePassword
     * @param array $credentials
     * @return array
     * @throws \Exception
     */
    public function aUserCanChangeProfile(array $credentials): array
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        $profile = [
            'test123' => sprintf('pr_%s', random_int(100000, 1000000 - 1)),
            'def' => 'lskdaölkd'
        ];
        $command = [
            'message_name' => 'changeUserProfile',
            'payload' => [
                'profile' => $profile
            ]
        ];

        $token = $this->getToken($username, $password);
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);
        self::assertInstanceOf(User::class, $user);
        self::assertEquals($profile, $user->getProfile());

        return ['username' => $username, 'password' => $password];
    }

    /**
     * @test
     * @depends aUserCanChangeProfile
     * @param array $credentials
     * @return array
     */
    public function aUserCanBeArchived(array $credentials): array
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        $command = [
            'message_name' => 'archiveUser',
            'payload' => []
        ];

        $token = $this->getToken($username, $password);
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);
        self::assertInstanceOf(User::class, $user);
        self::assertTrue($user->isArchived());

        return ['username' => $username, 'password' => $password];
    }

    /**
     * @test
     * @depends aUserCanBeArchived
     * @param array $credentials
     * @return array
     */
    public function aUserCanBeReactivated(array $credentials): array
    {
        $username = $credentials['username'];
        $password = $credentials['password'];

        $command = [
            'message_name' => 'reactivateUser',
            'payload' => []
        ];

        $token = $this->getToken($username, $password);
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);
        self::assertInstanceOf(User::class, $user);
        self::assertFalse($user->isArchived());

        return ['username' => $username, 'password' => $password];
    }

    /**
     * @test
     * @depends aUserCanBeReactivated
     * @param array $credentials
     * @throws \Exception
     */
    public function aUserCanBeDeletedByAnAdmin(array $credentials): void
    {
        
        $username = $credentials['username'];
        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);
        $user_id = $user->getId()->toString();


        $this->createUser('super_admin', 'admin', ['ROLE_ADMIN']);
        $command = [
            'message_name' => 'deleteUser',
            'payload' => [
                'user_id' => $user_id
            ]
        ];

        $token = $this->getToken('super_admin', 'admin');
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(202, $response->getStatusCode());

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->findOneByUsername($username);
        self::assertNull($user);
    }
}

