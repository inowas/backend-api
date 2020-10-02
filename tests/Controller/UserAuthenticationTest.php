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
}
