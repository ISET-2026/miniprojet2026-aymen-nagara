<?php

namespace App\Entity;

use App\Repository\CategorieRecetteRepository;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRecetteRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_categorie_nom', columns: ['nom'])]
#[ApiResource(
    routePrefix: '/api',
    operations: [
        new GetCollection(uriTemplate: '/categorie_recettes', normalizationContext: ['groups' => ['categorie:read']]),
        new Get(uriTemplate: '/categorie_recettes/{id}', normalizationContext: ['groups' => ['categorie:read']]),
        new Post(uriTemplate: '/categorie_recettes', denormalizationContext: ['groups' => ['categorie:write']]),
        new Delete(uriTemplate: '/categorie_recettes/{id}'),
    ],
)]
class CategorieRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categorie:read'])]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[ORM\Column(length: 50)]
    #[Groups(['categorie:read', 'categorie:write', 'recette:read'])]
    private ?string $nom = null;

    #[Assert\Length(max: 10)]
    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['categorie:read', 'categorie:write'])]
    private ?string $icone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['categorie:read', 'categorie:write'])]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Recette::class)]
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

    public function getIcone(): ?string
    {
        return $this->icone;
    }

    public function setIcone(?string $icone): static
    {
        $this->icone = $icone;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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
