<?php

namespace App\Entity;

use App\Repository\ForumRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=ForumRepository::class)
 * @ORM\Table(name="forums")
 */
class Forum
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Forum::class, inversedBy="Children")
     */
    private ?Forum $Parent;

    /**
     * @ORM\OneToMany(targetEntity=Forum::class, mappedBy="Parent")
     */
    private $Children;

    /**
     * @ORM\OneToOne(targetEntity=Channel::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $Channel;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Title;

    public function __construct()
    {
        $this->Children = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParent(): ?self
    {
        return $this->Parent;
    }

    public function setParent(?self $Parent): self
    {
        $this->Parent = $Parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->Children;
    }

    public function addChild(self $child): self
    {
        if (!$this->Children->contains($child)) {
            $this->Children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->Children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getChannel(): ?Channel
    {
        return $this->Channel;
    }

    public function setChannel(Channel $Channel): self
    {
        $this->Channel = $Channel;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(string $Title): self
    {
        $this->Title = $Title;

        return $this;
    }

    #[Pure]
    public function isChannel(): bool
    {
        return $this->getParent() == null;
    }

    public function getSlug(): string
    {
        return "slug";
    }
}
