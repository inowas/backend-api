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
        if (!$user instanceof User) {
            return new JsonResponse(null, Response::HTTP_FORBIDDEN);
        }

        $isPublic = $request->query->has('public') && $request->query->get('public') === 'true';

        switch ($tool) {
            case ('myTools'):
                $instances = array_merge(
                    $this->entityManager->getRepository(ModflowModel::class)->getAllToolsFromUser($user),
                    $this->entityManager->getRepository(Mcda::class)->getAllToolsFromUser($user),
                    $this->entityManager->getRepository(SimpleTool::class)->getAllToolsFromUser($user),
                );
                break;

            case ('T03'):
                $repository = $this->entityManager->getRepository(ModflowModel::class);
                $instances = $repository->getTool($tool, $user, $isPublic, false);
                break;

            case ('T05'):
                $repository = $this->entityManager->getRepository(Mcda::class);
                $instances = $repository->getTool($tool, $user, $isPublic, false);
                break;
            default:
                $repository = $this->entityManager->getRepository(SimpleTool::class);
                $instances = $repository->getTool($tool, $user, $isPublic, false);
        }

        return $this->createResponse($instances, $user);
    }

    private function createResponse(array $instances, User $user): JsonResponse
    {
        /** @var ToolInstance $instance */
        foreach ($instances as $key => $instance) {
            $instances[$key] = [
                'id' => $instance->id(),
                'tool' => $instance->tool(),
                'name' => $instance->name(),
                'description' => $instance->description(),
                'public' => $instance->isPublic(),
                'permissions' => $instance->getPermissions($user),
                'created_at' => $instance->createdAt()->format(DATE_ATOM),
                'updated_at' => $instance->updatedAt()->format(DATE_ATOM),
                'user_id' => $instance->getUserId(),
                'user_name' => $instance->getUsername()
            ];
        }

        return new JsonResponse($instances);
    }
}
