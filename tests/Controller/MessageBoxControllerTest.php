<?php

namespace App\Tests\Controller;

class MessageBoxControllerTest extends CommandTestBaseClass
{
    /**
     * @test
     */
    public function sendCommandWithoutTokenReturns401(): void
    {
        $client = $this->client;
        $client->request(
            'POST',
            '/v3/messagebox',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([], JSON_THROW_ON_ERROR)
        );

        self::assertEquals(401, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function sendInvalidCommandByValidUser(): void
    {
        $user = $this->createRandomUser();
        $command = [
            'message_name' => 'testMessage'
        ];

        $token = $this->getToken($user->getUsername(), $user->getPassword());
        $response = $this->sendCommand('/v3/messagebox', $command, $token);
        self::assertEquals(322, $response->getStatusCode());
    }
}
