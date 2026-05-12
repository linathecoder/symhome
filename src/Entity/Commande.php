<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

   
    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: LigneCommande::class)]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->ligneCommandes = new ArrayCollection();
    }

   
    public function getId(): ?int { return $this->id; }

    public function getDate(): ?\DateTime { return $this->date; }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getTotal(): ?float { return $this->total; }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getStatus(): ?string { return $this->status; }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}