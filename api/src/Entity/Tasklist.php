<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity("title")
 *
 * @ApiResource(
 *     collectionOperations={
 *         "get"={"normalization_context"={"groups"={"tasklist:get_collection:normalization"}}},
 *         "post"={
 *             "normalization_context"={"groups"={"tasklist:post:normalization"}},
 *             "denormalization_context"={"groups"={"tasklist:post:denormalization"}},
 *          },
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"={"groups"={"tasklist:get_item:normalization"}}},
 *         "patch"={
 *              "normalization_context"={"groups"={"tasklist:patch:normalization"}},
 *              "denormalization_context"={"groups"={"tasklist:patch:denormalization"}},
 *         },
 *         "delete"={"denormalization_context"={"groups"={"tasklist:delete:denormalization"}}},
 *     },
 *     attributes={
 *         "order"={"updatedAt": "DESC"},
 *     },
 * )
 */
class Tasklist
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @Groups({
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     *
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     *
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     * })
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     *
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     * })
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=64)
     *
     * @Groups({
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     *     "tasklist:post:denormalization",
     *     "tasklist:patch:denormalization",
     *
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     * })
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="tasklist", orphanRemoval=true)
     *
     * @Groups({
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     * })
     */
    private $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $now = new \DateTime('now');

        $this->setUpdatedAt($now);
        $this->setCreatedAt($now);
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setTasklist($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
            if ($task->getTasklist() === $this) {
                $task->setTasklist(null);
            }
        }

        return $this;
    }
}
