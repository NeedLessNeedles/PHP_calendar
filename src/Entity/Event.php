<?php

/**
 * Event entity.
 */

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class Event.
 *
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 *
 * @ORM\Table(name="event")
 */
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Title.
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Description.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * Location.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    /**
     * Start date.
     */
    #[ORM\Column]
    private ?\DateTime $startDate = null;

    /**
     * End date.
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTime $endDate = null;

    /**
     * Owner.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $owner = null;

    /**
     * Status.
     */
    #[ORM\Column(length: 30)]
    private ?string $status = null;

    /**
     * Category.
     */
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    /**
     * Tags.
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'events')]
    #[ORM\JoinTable(name: 'event_tag')]
    private Collection $tags;

    /**
     * Event constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string $title Title
     *
     * @return static Title
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Getter for description.
     *
     * @return string|null Description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Setter for description.
     *
     * @param string|null $description Description
     *
     * @return static Description
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Getter for location.
     *
     * @return string|null Location
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Setter for location.
     *
     * @param string|null $location Location
     *
     * @return static Location
     */
    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Getter for start date.
     *
     * @return \DateTime|null Start date
     */
    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    /**
     * Setter for start date.
     *
     * @param \DateTime $startDate Start date
     *
     * @return static Start date
     */
    public function setStartDate(\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Getter for end date.
     *
     * @return \DateTime|null End date
     */
    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    /**
     * Setter for end date.
     *
     * @param \DateTime|null $endDate End date
     *
     * @return static End date
     */
    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Getter for owner.
     *
     * @return User|null Owner
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * Setter for owner.
     *
     * @param User|null $owner Owner
     *
     * @return static Owner
     */
    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Getter for status.
     *
     * @return string|null Status
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Setter for status.
     *
     * @param string $status Status
     *
     * @return static Status
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Getter for category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for category.
     *
     * @param Category|null $category category
     *
     * @return static Category
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Getter for tag.
     *
     * @return Collection Tag
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag Tag
     *
     * @return static Tag
     */
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * Remove tag.
     *
     * @param Tag $tag Tag
     *
     * @return static Tag
     */
    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
