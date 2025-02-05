<?php

namespace App\Entity;

use App\Repository\BilletRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BilletRepository::class)]
class Billet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'prix')]
    private ?Matche $matche = null;

    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?int $placesDisponibles = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatche(): ?Matche
    {
        return $this->matche;
    }

    public function setMatche(?Matche $matche): static
    {
        $this->matche = $matche;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getPlacesDisponibles(): ?int
    {
        return $this->placesDisponibles;
    }

    public function setPlacesDisponibles(?int $placesDisponibles): static
    {
        $this->placesDisponibles = $placesDisponibles;

        return $this;
    }
}
