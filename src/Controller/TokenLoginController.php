<?php

namespace App\Controller;

use App\Service\UserManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class TokenLoginController
{

    private UserManager $um;
    private JWTTokenManagerInterface $JWTManager;


    public function __construct(UserManager $um, JWTTokenManagerInterface $JWTManager)
    {
        $this->um = $um;
        $this->JWTManager = $JWTManager;
    }

    /**
     * @Route("/token_login", name="token_login", methods={"POST", "GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function __invoke(Request $request): JsonResponse
    {

        $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $userId = $body['user_id'] ?? null;
        $token = $body['token'] ?? null;

        if ($userId === null) {
            return new JsonResponse(null, 401);
        }

        $user = $this->um->findUserById($userId);
        if ($user === null) {
            return new JsonResponse(null, 401);
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new JsonResponse('You are admin user, please register with your username and password.', 401);
        }

        if (!($token === $user->getLoginToken())) {
            return new JsonResponse(null, 401);
        }

        return new JsonResponse(['token' => $this->JWTManager->create($user)]);
    }
}
