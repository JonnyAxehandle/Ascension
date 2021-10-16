<?php

namespace App\Entity;

use App\Repository\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ThreadRepository::class)
 * @ORM\Table(name="core_threads")
 */
class Thread
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Channel::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Channel $Channel;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $Title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $Description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $AuthorName;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $DateStarted;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $LastPostDate;

    /**
     * @ORM\ManyToOne(targetEntity=Channel::class, inversedBy="Threads")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Channel $channel;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="Thread")
     */
    private Collection $Posts;

    public function __construct()
    {
        $this->DateStarted = new \DateTime();
        $this->LastPostDate = new \DateTime();
        $this->Posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChannel(): ?Channel
    {
        return $this->Channel;
    }

    public function setChannel(?Channel $Channel): self
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

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(?string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->AuthorName;
    }

    public function setAuthorName(?string $AuthorName): self
    {
        $this->AuthorName = $AuthorName;

        return $this;
    }

    public function getDateStarted(): ?\DateTimeInterface
    {
        return $this->DateStarted;
    }

    public function setDateStarted(\DateTimeInterface $DateStarted): self
    {
        $this->DateStarted = $DateStarted;

        return $this;
    }

    public function getLastPostDate(): ?\DateTimeInterface
    {
        return $this->LastPostDate;
    }

    public function setLastPostDate(\DateTimeInterface $LastPostDate): self
    {
        $this->LastPostDate = $LastPostDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return "threadSlug";
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->Posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->Posts->contains($post)) {
            $this->Posts[] = $post;
            $post->setThread($this);

            $this->setLastPostDate($post->getDatePosted());
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->Posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getThread() === $this) {
                $post->setThread(null);
            }
        }

        return $this;
    }
}
