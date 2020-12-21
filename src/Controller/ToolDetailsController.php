<?php

namespace App\Controller;

use App\Model\Mcda\Mcda;
use App\Model\Modflow\ModflowModel;
use App\Model\SimpleTool\SimpleTool;
use App\Model\ToolInstance;
use App\Model\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


class ToolDetailsController
{
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/tools/{tool}/{id}", name="tool_data", methods={"GET"})
     * @param Request $request
     * @param string $tool
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $tool, string $id): JsonResponse
    {
        /** @var TokenInterface $token */
        $token = $this->tokenStorage->getToken();

        /** @var User $user */
        $user = $token->getUser();

        switch ($tool) {
            case ('T03'):
                $toolClass = ModflowModel::class;
                break;
            case ('T05'):
                $toolClass = Mcda::class;
                break;
            default:
                $toolClass = SimpleTool::class;
        }

        /** @var ToolInstance $toolInstance */
        $toolInstance = $this->entityManager->getRepository($toolClass)->findOneBy(['id' => $id]);

        $permissions = $toolInstance->getPermissions($user);
        if ($permissions === '---') {
            return new JsonResponse([]);
        }

        $result = [
            'id' => $toolInstance->id(),
            'tool' => $toolInstance->tool(),
            'name' => $toolInstance->name(),
            'description' => $toolInstance->description(),
            'public' => $toolInstance->isPublic(),
            'permissions' => $permissions,
            'created_at' => $toolInstance->createdAt()->format(DATE_ATOM),
            'updated_at' => $toolInstance->updatedAt()->format(DATE_ATOM),
            'user_id' => $toolInstance->getUserId(),
            'user_name' => $toolInstance->getUsername(),
            'data' => $toolInstance->data()
        ];

        return new JsonResponse($result);
    }
}
