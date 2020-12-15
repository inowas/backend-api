<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\ToolInstance\Command\AddBoundaryCommand;
use App\Domain\ToolInstance\Command\AddLayerCommand;
use App\Domain\ToolInstance\Command\CloneLayerCommand;
use App\Domain\ToolInstance\Command\CloneModflowModelCommand;
use App\Domain\ToolInstance\Command\CloneScenarioAnalysisCommand;
use App\Domain\ToolInstance\Command\CloneToolInstanceCommand;
use App\Domain\ToolInstance\Command\CreateModflowModelCommand;
use App\Domain\ToolInstance\Command\CreateScenarioAnalysisCommand;
use App\Domain\ToolInstance\Command\CreateScenarioCommand;
use App\Domain\ToolInstance\Command\CreateToolInstanceCommand;
use App\Domain\ToolInstance\Command\DeleteModflowModelCommand;
use App\Domain\ToolInstance\Command\DeleteScenarioAnalysisCommand;
use App\Domain\ToolInstance\Command\DeleteScenarioCommand;
use App\Domain\ToolInstance\Command\DeleteToolInstanceCommand;
use App\Domain\ToolInstance\Command\ImportModflowModelCommand;
use App\Domain\ToolInstance\Command\RemoveBoundaryCommand;
use App\Domain\ToolInstance\Command\RemoveLayerCommand;
use App\Domain\ToolInstance\Command\UpdateBoundaryCommand;
use App\Domain\ToolInstance\Command\UpdateLayerCommand;
use App\Domain\ToolInstance\Command\UpdateModflowModelCalculationIdCommand;
use App\Domain\ToolInstance\Command\UpdateModflowModelMetadataCommand;
use App\Domain\ToolInstance\Command\UpdateModflowModelDiscretizationCommand;
use App\Domain\ToolInstance\Command\UpdateFlopyPackagesCommand;
use App\Domain\ToolInstance\Command\UpdateSoilmodelPropertiesCommand;
use App\Domain\ToolInstance\Command\UpdateStressperiodsCommand;
use App\Domain\ToolInstance\Command\UpdateToolInstanceCommand;
use App\Domain\ToolInstance\Command\UpdateToolInstanceDataCommand;
use App\Domain\ToolInstance\Command\UpdateToolInstanceMetadataCommand;
use App\Domain\ToolInstance\Command\UpdateTransportCommand;
use App\Domain\ToolInstance\Command\UpdateVariableDensityCommand;
use App\Domain\User\Command\CreateUserCommand;
use App\Domain\User\Command\DisableUserCommand;
use App\Domain\User\Command\EnableUserCommand;
use App\Domain\User\Command\RevokeLoginTokenCommand;
use App\Model\User;
use App\Model\Command;
use App\Domain\User\Command\ArchiveUserCommand;
use App\Domain\User\Command\ChangeUsernameCommand;
use App\Domain\User\Command\ChangeUserPasswordCommand;
use App\Domain\User\Command\ChangeUserProfileCommand;
use App\Domain\User\Command\DeleteUserCommand;
use App\Domain\User\Command\DemoteUserCommand;
use App\Domain\User\Command\PromoteUserCommand;
use App\Domain\User\Command\ReactivateUserCommand;

use Symfony\Component\Security\Core\Security;
use function json_decode;
use RuntimeException;

use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class MessageBoxController
{
    /** @var MessageBusInterface */
    private MessageBusInterface $commandBus;

    /** @var TokenStorageInterface */
    private TokenStorageInterface $tokenStorage;

    /** @var Security */
    private Security $security;

    /** @var array */
    private array $availableCommands = [];

    public function __construct(
        MessageBusInterface $bus,
        TokenStorageInterface $tokenStorage,
        Security $security
    )
    {
        $this->commandBus = $bus;
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/messagebox", name="messagebox", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function messagebox(Request $request): JsonResponse
    {
        if (null === $this->security->getUser()) {
            return new JsonResponse(['message' => 'Wrong Credentials'], 401);
        }

        $availableCommands = [
            ArchiveUserCommand::class,
            ChangeUsernameCommand::class,
            ChangeUserPasswordCommand::class,
            ChangeUserProfileCommand::class,
            CreateUserCommand::class,
            DeleteUserCommand::class,
            DemoteUserCommand::class,
            DisableUserCommand::class,
            EnableUserCommand::class,
            PromoteUserCommand::class,
            ReactivateUserCommand::class,
            RevokeLoginTokenCommand::class,

            AddBoundaryCommand::class,
            AddLayerCommand::class,
            CloneLayerCommand::class,
            CloneModflowModelCommand::class,
            CloneToolInstanceCommand::class,
            CreateModflowModelCommand::class,
            CreateToolInstanceCommand::class,
            DeleteModflowModelCommand::class,
            DeleteToolInstanceCommand::class,
            ImportModflowModelCommand::class,
            RemoveBoundaryCommand::class,
            RemoveLayerCommand::class,
            UpdateBoundaryCommand::class,
            UpdateFlopyPackagesCommand::class,
            UpdateLayerCommand::class,
            UpdateModflowModelCalculationIdCommand::class,
            UpdateModflowModelDiscretizationCommand::class,
            UpdateModflowModelMetadataCommand::class,
            UpdateTransportCommand::class,
            UpdateVariableDensityCommand::class,
            UpdateSoilmodelPropertiesCommand::class,
            UpdateStressperiodsCommand::class,

            UpdateToolInstanceCommand::class,
            UpdateToolInstanceDataCommand::class,
            UpdateToolInstanceMetadataCommand::class,

            CloneScenarioAnalysisCommand::class,
            CreateScenarioAnalysisCommand::class,
            CreateScenarioCommand::class,
            DeleteScenarioAnalysisCommand::class,
            DeleteScenarioCommand::class
        ];
        $this->setAvailableCommands($availableCommands);

        try {
            $this->assertIsValidRequest($request);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        /** @var User $user */
        $user = $token->getUser();

        # extract message
        $message = $this->getMessage($request);
        $messageName = $message['message_name'];
        $payload = $message['payload'];

        try {
            /** @var Command $commandClass */
            $commandClass = $this->availableCommands[$messageName];
            $commandClass::getJsonSchema() && $this->validateSchema($commandClass::getJsonSchema(), $request->getContent());
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 422);
        }

        /** @var Command $command */
        $command = $commandClass::fromPayload($payload);
        $command->withAddedMetadata('user_id', $user->getId()->toString());
        $command->withAddedMetadata('is_admin', in_array('ROLE_ADMIN', $user->getRoles(), true));

        try {
            $this->commandBus->dispatch($command);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode() !== 0 ? $e->getCode() : 500);
        }

        return new JsonResponse([], 202);
    }

    private function setAvailableCommands(array $availableCommands): void
    {
        foreach ($availableCommands as $command) {
            $this->availableCommands[$command::getMessageName()] = $command;
        }
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    private function assertIsValidRequest(Request $request): void
    {

        if (0 !== strpos($request->headers->get('Content-Type'), 'application/json')) {
            throw new RuntimeException('Expecting Header: Content-Type: application/json');
        }

        $body = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON received.');
        }

        $message_name = $body['message_name'] ?? null;

        if (!$message_name) {
            throw new RuntimeException(sprintf('Parameter message_name not given or null.'));
        }

        if (!array_key_exists($message_name, $this->availableCommands)) {
            throw new RuntimeException(
                sprintf(
                    'MessageName: %s not in the list of available commands. Available commands are: %s.',
                    $message_name, implode(', ', array_keys($this->availableCommands))
                )
            );
        }

        $payload = $body['payload'] ?? null;

        if (null === $payload) {
            throw new RuntimeException('Parameter payload expected.');
        }
    }

    private function getMessage(Request $request): array
    {
        return json_decode($request->getContent(), true);
    }

    /**
     * @param $schema
     * @param $content
     * @throws Exception
     * @throws InvalidValue
     */
    private function validateSchema(string $schema, string $content): void
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $schema = Schema::import($schema);

        /** @noinspection JsonEncodingApiUsageInspection */
        $schema->in(json_decode($content));
    }
}
