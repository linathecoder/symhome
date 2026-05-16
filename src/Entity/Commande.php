<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(length: 50)]
    private ?string $status = 'en_attente';

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'commande', targetEntity: LigneCommande::class, cascade: ['persist', 'remove'])]
    private Collection $ligneCommandes;

    public function __construct()
    {
        $this->ligneCommandes = new ArrayCollection();
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getLigneCommandes(): Collection
    {
        return $this->ligneCommandes;
    }

    public function addLigneCommande(LigneCommande $ligne): static
    {
        if (!$this->ligneCommandes->contains($ligne)) {
            $this->ligneCommandes->add($ligne);
            $ligne->setCommande($this);
        }
        return $this;
    }

    public function removeLigneCommande(LigneCommande $ligne): static
    {
        if ($this->ligneCommandes->removeElement($ligne)) {
            if ($ligne->getCommande() === $this) {
                $ligne->setCommande(null);
            }
        }
        return $this;
    }
}
