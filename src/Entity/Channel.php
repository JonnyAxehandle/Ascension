<?php

namespace App\Entity;

use App\Repository\ChannelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChannelRepository::class)
 * @ORM\Table(name="core_channels")
 */
class Channel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\OneToMany(targetEntity=Thread::class, mappedBy="channel")
     */
    private $Threads;

    public function __construct()
    {
        $this->Threads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Thread[]
     */
    public function getThreads(): Collection
    {
        return $this->Threads;
    }

    public function addThread(Thread $thread): self
    {
        if (!$this->Threads->contains($thread)) {
            $this->Threads[] = $thread;
            $thread->setChannel($this);
        }

        return $this;
    }

    public function removeThread(Thread $thread): self
    {
        if ($this->Threads->removeElement($thread)) {
            // set the owning side to null (unless already changed)
            if ($thread->getChannel() === $this) {
                $thread->setChannel(null);
            }
        }

        return $this;
    }
}
