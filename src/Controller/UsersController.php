<?php

namespace App\Controller;

use App\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class UsersController
{

    /** @var TokenStorageInterface */
    private TokenStorageInterface $tokenStorage;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $em;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    /**
     * @Route("/users", name="users", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(null, 401);
        }

        if (!in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse(null, 403);
        }

        $users = $this->em->getRepository('App:User')->findAll();
        $response = [];

        foreach ($users as $user) {
            $response[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'profile' => $user->getProfile(),
                'enabled' => $user->isEnabled(),
            ];
        }

        return new JsonResponse($response);
    }
}
