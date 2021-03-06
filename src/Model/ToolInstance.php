<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JsonSerializable;
use Ramsey\Uuid\Uuid;


/**
 * @ORM\MappedSuperclass()
 * @ORM\HasLifecycleCallbacks
 */
abstract class ToolInstance implements JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="string", unique=true, nullable=false)
     */
    protected string $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Model\User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id", onDelete="SET NULL")
     */
    protected User $user;

    /**
     * @var string
     *
     * @ORM\Column(name="tool", type="string", length=36, nullable=false)
     */
    protected string $tool;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected string $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    protected string $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=false)
     */
    protected bool $isPublic;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_archived", type="boolean", nullable=false)
     */
    protected bool $isArchived;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_scenario", type="boolean", nullable=false)
     */
    protected bool $isScenario;

    /**
     * @var ?DateTimeImmutable
     *
     * @ORM\Column(name="created_at", type="datetime_immutable", nullable=false)
     */
    protected ?DateTimeImmutable $createdAt = null;

    /**
     * @var ?DateTimeImmutable
     *
     * @ORM\Column(name="updated_at", type="datetime_immutable", nullable=false)
     */
    protected ?DateTimeImmutable $updatedAt = null;

    public function __clone()
    {
        $this->id = Uuid::uuid4()->toString();
        $this->createdAt = new DateTimeImmutable();
    }

    public static function createWithParams(string $id, User $user, string $tool, ToolMetadata $metadata): self
    {
        $static = new static();
        $static->id = $id;
        $static->user = $user;
        $static->tool = $tool;
        $static->name = $metadata->name();
        $static->description = $metadata->description();
        $static->isPublic = $metadata->isPublic();
        $static->isArchived = false;
        $static->isScenario = false;
        return $static;
    }

    protected function __construct()
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function tool(): string
    {
        return $this->tool;
    }

    public function metadata(): ToolMetadata
    {
        return ToolMetadata::fromParams($this->name, $this->description, $this->isPublic);
    }

    public function userId(): ?string
    {
        return $this->getUser() instanceof User ? $this->getUser()->getId()->toString() : null;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getUsername(): string
    {
        if (null === $this->getUser()) {
            return '';
        }

        return $this->getUser()->getUsername();
    }

    public function getUserId(): string
    {
        if (null === $this->getUser()) {
            return '';
        }

        return $this->getUser()->getId()->toString();
    }

    public function setMetadata(ToolMetadata $metadata): void
    {
        $this->name = $metadata->name();
        $this->description = $metadata->description();
        $this->isPublic = $metadata->isPublic();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function isArchived(): bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): void
    {
        $this->isArchived = $isArchived;
    }

    public function isScenario(): bool
    {
        return $this->isScenario;
    }

    public function setIsScenario(bool $isScenario): void
    {
        $this->isScenario = $isScenario;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    abstract public function data(): array;

    abstract public function setData(array $data): void;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @throws Exception
     */
    public function updateTimestamps(): void
    {
        $this->setUpdatedAt(new DateTimeImmutable('now'));
        if ($this->createdAt === null) {
            $this->setCreatedAt(new DateTimeImmutable('now'));
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId(),
            'tool' => $this->tool,
            'name' => $this->name,
            'description' => $this->description,
            'isPublic' => $this->isPublic,
            'isArchived' => $this->isArchived,
            'isScenario' => $this->isScenario,
            'createdAt' => $this->createdAt()->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt()->format(DATE_ATOM)
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function getPermissions($user): string
    {
        $permissions = '---';

        if ($this->isPublic()) {
            $permissions = 'r--';
        }

        if ($user instanceof User && $this->userId() === $user->getId()->toString()) {
            $permissions = 'rwx';
        }

        return $permissions;
    }
}
