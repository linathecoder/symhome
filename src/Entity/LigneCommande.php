<?php

namespace App\Entity;

use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LigneCommandeRepository::class)]
class LigneCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // 🔗 Many lignes belong to one commande
    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    // 🔗 Many lignes refer to one meuble
    #[ORM\ManyToOne(inversedBy: 'ligneCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meuble $meuble = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $price = null;

    // ================= GETTERS & SETTERS =================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;
        return $this;
    }

    public function getMeuble(): ?Meuble
    {
        return $this->meuble;
    }

    public function setMeuble(?Meuble $meuble): static
    {
        $this->meuble = $meuble;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;
        return $this;
    }
}