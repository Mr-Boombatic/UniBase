<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\p{Lu}\p{Ll}+$/u',
        message: 'The project name must begin with a capital letter and contain only alphabetic characters.'
    )]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\p{Lu}\p{Ll}+$/u',
        message: "The buyer's name must begin with a capital letter and contain only alphabetic characters."
    )]
    #[ORM\Column(length: 255)]
    private ?string $customer = null;

    #[ORM\Column]
    private ?bool $isClosed = false;

    /**
     * @var Collection<int, Worker>
     */
    #[ORM\ManyToMany(targetEntity: Worker::class, inversedBy: 'workers')]
    private Collection $workers;

    public function __construct()
    {
        $this->workers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCustomer(): ?string
    {
        return $this->customer;
    }

    public function setCustomer(string $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function isClosed(): ?bool
    {
        return $this->isClosed;
    }

    public function setClosed(): static
    {
        $this->isClosed = true;

        return $this;
    }

    /**
     * @return Collection<int, Worker>
     */
    public function getWorkers(): Collection
    {
        return $this->workers;
    }

    public function addWorker(Worker $worker): static
    {
        if (!$this->workers->contains($worker)) {
            $this->workers->add($worker);
        }

        return $this;
    }

    public function removeWorker(Worker $worker): static
    {
        $this->workers->removeElement($worker);

        return $this;
    }
}
