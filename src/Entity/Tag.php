<?php

/**
 * Tag entity.
 */

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class Tag.
 *
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 *
 * @ORM\Table(name="tag")
 */
#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
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
    #[ORM\Column(length: 64)]
    private ?string $title = null;

    /**
     * Events.
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'tags')]
    private Collection $events;

    /**
     * Tag constructor.
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
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
     * Getter for events.
     *
     * @return Collection Events
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * Add event.
     *
     * @param Event $event Event
     *
     * @return static Event
     */
    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->addTag($this);
        }

        return $this;
    }

    /**
     * Remove event.
     *
     * @param Event $event Event
     *
     * @return static Event
     */
    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            $event->removeTag($this);
        }

        return $this;
    }
}
