<?php

namespace App\Entity;

use App\Repository\TagRecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRecetteRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_tag_nom', columns: ['nom'])]
class TagRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50)]
    #[Groups(['recette:read'])]
    private ?string $nom = null;

    #[Assert\Regex(pattern: '/^#[0-9a-fA-F]{6}$/', message: 'Couleur hex (#RRGGBB) attendue.')]
    #[ORM\Column(length: 7)]
    private ?string $couleur = '#888888';

    #[ORM\ManyToMany(targetEntity: Recette::class, mappedBy: 'tags')]
    private Collection $recettes;

    public function __construct()
    {
        $this->recettes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): static
    {
        $this->couleur = $couleur;

        return $this;
    }

    /** @return Collection<int, Recette> */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function __toString(): string
    {
        return (string) $this->nom;
    }
}
