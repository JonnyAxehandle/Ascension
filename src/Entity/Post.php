<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 * @ORM\Table(name="core_posts")
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Thread::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Thread $Thread;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $AuthorName;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $Content;

    /**
     * @ORM\Column(type="date")
     */
    private ?\DateTimeInterface $DatePosted;

    public function __construct()
    {
        $this->DatePosted = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getThread(): ?Thread
    {
        return $this->Thread;
    }

    public function setThread(?Thread $Thread): self
    {
        $this->Thread = $Thread;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->AuthorName;
    }

    public function setAuthorName(string $AuthorName): self
    {
        $this->AuthorName = $AuthorName;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->Content;
    }

    public function setContent(string $Content): self
    {
        $this->Content = $Content;

        return $this;
    }

    public function getDatePosted(): ?\DateTimeInterface
    {
        return $this->DatePosted;
    }

    public function setDatePosted(\DateTimeInterface $DatePosted): self
    {
        $this->DatePosted = $DatePosted;

        return $this;
    }
}
