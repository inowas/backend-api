<?php

namespace App\Controller;

use App\Model\Mcda\Mcda;
use App\Model\Modflow\ModflowModel;
use App\Model\SimpleTool\SimpleTool;
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
     * @Route("/users/{id}", name="users", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $id = null): JsonResponse
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

        if (!$id) {
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

        $user = $this->em->getRepository('App:User')->findOneBy(['id' => $id]);

        if (!$user) {
            return new JsonResponse(null, 404);
        }

        $toolInstances = array_merge(
            $this->em->getRepository(Mcda::class)->getAllToolsFromUser($user),
            $this->em->getRepository(ModflowModel::class)->getAllToolsFromUser($user),
            $this->em->getRepository(SimpleTool::class)->getAllToolsFromUser($user),
        );

        $tools = [];
        foreach ($toolInstances as $toolInstance) {
            $tools[] = [
                'id' => $toolInstance->id(),
                'tool' => $toolInstance->tool(),
                'name' => $toolInstance->name(),
                'description' => $toolInstance->description(),
                'public' => $toolInstance->isPublic(),
                'user_id' => $toolInstance->userId(),
                'user_name' => $toolInstance->getUsername(),
                'created_at' => $toolInstance->createdAt()->format(DATE_ATOM),
                'updated_at' => $toolInstance->getUpdatedAt()->format(DATE_ATOM)
            ];
        }

        $response = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'profile' => $user->getProfile(),
            'enabled' => $user->isEnabled(),
            'tools' => $tools
        ];

        return new JsonResponse($response);
    }
}
