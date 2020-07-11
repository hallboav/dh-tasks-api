<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
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
 *         "get"={"normalization_context"={"groups"={"task:get_collection:normalization"}}},
 *         "post"={
 *             "normalization_context"={"groups"={"task:post:normalization"}},
 *             "denormalization_context"={"groups"={"task:post:denormalization"}},
 *         },
 *     },
 *     itemOperations={
 *         "get"={"normalization_context"={"groups"={"task:get_item:normalization"}}},
 *         "patch"={
 *             "normalization_context"={"groups"={"task:patch:normalization"}},
 *             "denormalization_context"={"groups"={"task:patch:denormalization"}},
 *         },
 *         "delete"={"denormalization_context"={"groups"={"task:delete:denormalization"}}},
 *     },
 *     attributes={
 *         "order"={"createdAt": "DESC"},
 *     },
 * )
 *
 * @ApiFilter(BooleanFilter::class, properties={"done"})
 */
class Task
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @Groups({
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     *
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     * })
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     *
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     * })
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     *
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     * })
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Assert\Type("bool")
     *
     * @Groups({
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     *     "task:patch:denormalization",
     *
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     * })
     */
    private $done = false;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=64)
     *
     * @Groups({
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:post:denormalization",
     *     "task:patch:normalization",
     *     "task:patch:denormalization",
     *
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     * })
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\Length(max=255)
     *
     * @Groups({
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     *     "task:post:denormalization",
     *     "task:patch:denormalization",
     *
     *     "tasklist:get_item:normalization",
     *     "tasklist:get_collection:normalization",
     *     "tasklist:post:normalization",
     *     "tasklist:patch:normalization",
     * })
     */
    private $details;

    /**
     * @ORM\ManyToOne(targetEntity=Tasklist::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotNull()
     *
     * @Groups({
     *     "task:get_item:normalization",
     *     "task:get_collection:normalization",
     *     "task:post:normalization",
     *     "task:patch:normalization",
     *     "task:post:denormalization",
     *     "task:patch:denormalization",
     * })
     */
    private $tasklist;

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

    public function getDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

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

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getTasklist(): ?Tasklist
    {
        return $this->tasklist;
    }

    public function setTasklist(?Tasklist $taskList): self
    {
        $this->tasklist = $taskList;

        return $this;
    }
}
