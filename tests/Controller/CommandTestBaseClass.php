<?php

namespace App\Tests\Controller;

use App\Model\SimpleTool\SimpleTool;
use App\Model\ToolMetadata;
use App\Model\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CommandTestBaseClass extends WebTestCase
{
    protected ?EntityManager $em;
    protected ?KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->client->followRedirects(true);
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @return User
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function createRandomUser(): User
    {
        $em = $this->em;
        $username = sprintf('newUser_%d', random_int(1000000, 10000000 - 1));
        $password = sprintf('newUserPassword_%d', random_int(1000000, 10000000 - 1));
        $user = new User($username, $password, ['ROLE_USER']);
        $em->persist($user);
        $em->flush($user);
        return $user;
    }

    /**
     * @param User $user
     * @param bool $isPublic
     * @return SimpleTool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function createSimpleTool(User $user, bool $isPublic = true): SimpleTool
    {
        $id = Uuid::uuid4()->toString();
        $simpleTool = SimpleTool::createWithParams($id, $user, 'T02', ToolMetadata::fromParams(
            'Tool01_' . random_int(10000, 99999),
            'Description_' . random_int(10000, 99999),
            $isPublic
        ));
        $simpleTool->setData(['123' => 123]);

        $em = $this->em;
        $em->persist($simpleTool);
        $em->flush($simpleTool);
        return $simpleTool;
    }

    /**
     * @param $em
     * @param $username
     * @param $password
     * @param array $roles
     * @return User
     * @throws ORMException
     */
    protected function createUser(string $username, string $password, $roles = ['ROLE_USER']): User
    {
        $user = new User($username, $password, $roles);
        $em = $this->em;
        $em->persist($user);
        $em->flush($user);
        return $user;
    }

    /**
     * @param $username
     * @param $password
     * @return string
     * @throws \JsonException
     */
    protected function getToken($username, $password): string
    {
        $client = $this->client;
        $client->request(
            'POST',
            '/v3/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(["username" => $username, "password" => $password], JSON_THROW_ON_ERROR)
        );

        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        return $content['token'];
    }

    /**
     * @param $endpoint
     * @param $command
     * @param null $token
     * @return Response
     * @throws \JsonException
     */
    protected function sendCommand($endpoint, $command, $token = null): Response
    {
        $client = $this->client;
        $headers = $token ? ['HTTP_Authorization' => sprintf('Bearer %s', $token)] : [];
        $headers['CONTENT_TYPE'] = 'application/json';
        $client->request(
            'POST',
            $endpoint,
            [],
            [],
            $headers,
            json_encode($command, JSON_THROW_ON_ERROR)
        );

        return $client->getResponse();
    }

    /**
     * @param $endpoint
     * @param null $token
     * @return Response
     */
    protected function sendRequest($endpoint, $token = null): Response
    {
        $client = $this->client;
        $headers = $token ? ['HTTP_Authorization' => sprintf('Bearer %s', $token)] : [];
        $headers['CONTENT_TYPE'] = 'application/json';
        $client->request(
            'GET',
            $endpoint,
            [],
            [],
            $headers
        );

        return $client->getResponse();
    }

    /**
     * @param $endpoint
     * @param $content
     * @param null $token
     * @return Response
     */
    protected function sendPostRequest($endpoint, $content, $token = null): Response
    {
        $client = $this->client;
        $headers = $token ? ['HTTP_Authorization' => sprintf('Bearer %s', $token)] : [];
        $headers['CONTENT_TYPE'] = 'application/json';
        $client->request(
            'POST',
            $endpoint,
            [],
            [],
            $headers,
            $content
        );

        return $client->getResponse();
    }
}
