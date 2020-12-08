<?php

namespace App\Tests\Controller;

use Exception;

class UserAuthenticationTest extends CommandTestBaseClass
{
    public function authenticationProvider(): array
    {
        return [
            ['admin', 'admin_pw', ['ROLE_ADMIN'], 200],
            ['user', 'user_pw', ['ROLE_USER'], 403]
        ];
    }

    /**
     * @dataProvider authenticationProvider
     * @param $username
     * @param $password
     * @param $roles
     * @param $statusCode
     * @throws Exception
     */
    public function testAuthentication($username, $password, $roles, $statusCode): void
    {
        $client = $this->client;
        $this->createUser($username, $password, $roles);
        $token = $this->getToken($username, $password);

        $client->request(
            'GET',
            '/v3/users.json',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Authorization' => sprintf('Bearer %s', $token)
            ]
        );

        self::assertEquals($statusCode, $client->getResponse()->getStatusCode());
    }

    public function testLoginLink(): void
    {
        $username = sprintf("test_user_%s", random_int(1000000, 9999999));
        $password = sprintf("test_password_%s", random_int(1000000, 9999999));
        $roles = ['ROLE_USER'];

        $client = $this->client;
        $user = $this->createUser($username, $password, $roles);
        $client->request(
            'POST',
            '/v3/token_login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => $user->getId()->toString(),
                'token' => $user->getLoginToken(),
            ], JSON_THROW_ON_ERROR)
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('token', $content);
        $token = $content['token'];

        $client->request(
            'GET',
            '/v3/user',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_Authorization' => sprintf('Bearer %s', $token)
            ]
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
