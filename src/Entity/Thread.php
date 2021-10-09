<?php

namespace App\Entity;

use App\Repository\ThreadRepository;
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
    private $id;

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
     * @ORM\Column(type="date")
     */
    private ?\DateTimeInterface $DateStarted;

    /**
     * @ORM\Column(type="date")
     */
    private ?\DateTimeInterface $LastPostDate;

    public function __construct()
    {
        $this->DateStarted = new \DateTime();
        $this->LastPostDate = new \DateTime();
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
}
