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


class ToolsController
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;


    public function __construct(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/tools", name="tools_list", methods={"GET"})
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

        $tool = $request->query->get('tool', null);

        switch ($tool) {
            case 'T03':
                $toolInstances = $this->entityManager->getRepository(ModflowModel::class)->getAllTools();
                break;
            case 'T05':
                $toolInstances = $this->entityManager->getRepository(Mcda::class)->getAllTools();
                break;
            case 'all':
                $toolInstances = array_merge(
                    $this->entityManager->getRepository(Mcda::class)->getAllTools(),
                    $this->entityManager->getRepository(ModflowModel::class)->getAllTools(),
                    $this->entityManager->getRepository(SimpleTool::class)->getAllTools()
                );
                break;
            default:
                $toolInstances = $this->entityManager->getRepository(SimpleTool::class)->getAllTools();
                break;
        }


        $result = [];
        foreach ($toolInstances as $toolInstance) {
            if ($toolInstance instanceof ToolInstance) {
                $result[] = [
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
        }

        return new JsonResponse($result);
    }
}
