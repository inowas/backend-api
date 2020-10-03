<?php

namespace App\Controller;

use App\Model\Mcda\Mcda;
use App\Model\Modflow\ModflowModel;
use App\Model\SimpleTool\SimpleTool;
use App\Model\ToolInstance;
use App\Model\User;
use App\Repository\ToolRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


final class DashboardController
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var TokenStorageInterface */
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
     * @Route("/tools/{tool}", name="dashboard", methods={"GET"})
     * @param Request $request
     * @param string $tool
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $tool): JsonResponse
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return new JsonResponse(['message' => 'error'], Response::HTTP_FORBIDDEN);
        }

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

        $isPublic = $request->query->has('public') && $request->query->get('public') === 'true';

        /** @var ToolRepositoryInterface $repository */
        $repository = $this->entityManager->getRepository($toolClass);
        $instances = $repository->getTool($tool, $user, $isPublic, false);
        return $this->createResponse($instances);
    }

    private function createResponse(array $instances): JsonResponse
    {
        /** @var ToolInstance $instance */
        foreach ($instances as $key => $instance) {
            $instances[$key] = [
                'id' => $instance->id(),
                'tool' => $instance->tool(),
                'name' => $instance->name(),
                'description' => $instance->description(),
                'created_at' => $instance->createdAt()->format(DATE_ATOM),
                'updated_at' => $instance->createdAt()->format(DATE_ATOM),
                'user_name' => $instance->getUsername()
            ];
        }

        return new JsonResponse($instances);
    }
}
