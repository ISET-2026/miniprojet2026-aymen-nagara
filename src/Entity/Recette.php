<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecetteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    routePrefix: '/api',
    operations: [
        new GetCollection(uriTemplate: '/recettes', normalizationContext: ['groups' => ['recette:read']]),
        new Get(uriTemplate: '/recettes/{id}', normalizationContext: ['groups' => ['recette:read']]),
        new Post(
            uriTemplate: '/recettes',
            denormalizationContext: ['groups' => ['recette:write']],
            normalizationContext: ['groups' => ['recette:read']]
        ),
        new Put(
            uriTemplate: '/recettes/{id}',
            denormalizationContext: ['groups' => ['recette:write']],
            normalizationContext: ['groups' => ['recette:read']]
        ),
        new Patch(
            uriTemplate: '/recettes/{id}',
            denormalizationContext: ['groups' => ['recette:write']],
            normalizationContext: ['groups' => ['recette:read']]
        ),
        new Delete(uriTemplate: '/recettes/{id}'),
    ],
)]
class Recette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['recette:read'])]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    #[ORM\Column(length: 255)]
    #[Groups(['recette:read', 'recette:write'])]
    private ?string $titre = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 30)]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['recette:read', 'recette:write'])]
    private ?string $description = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['recette:read', 'recette:write'])]
    private ?string $instructions = null;

    #[Assert\Range(min: 1)]
    #[ORM\Column]
    #[Groups(['recette:read', 'recette:write'])]
    private int $tempsPreparation = 1;

    #[Assert\GreaterThanOrEqual(0)]
    #[ORM\Column(nullable: true)]
    #[Groups(['recette:read', 'recette:write'])]
    private ?int $tempsCuisson = null;

    #[Assert\Choice(choices: ['facile', 'moyen', 'difficile'])]
    #[ORM\Column(length: 20)]
    #[Groups(['recette:read', 'recette:write'])]
    private string $difficulte = 'facile';

    #[Assert\Range(min: 1, max: 50)]
    #[ORM\Column]
    #[Groups(['recette:read', 'recette:write'])]
    private int $nbPersonnes = 1;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['recette:read'])]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column]
    #[Groups(['recette:read'])]
    private bool $publiee = false;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['recette:read'])]
    private ?string $imageName = null;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['recette:read'])]
    private ?CategorieRecette $categorie = null;

    #[ORM\ManyToOne(inversedBy: 'recettes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $auteur = null;

    #[ORM\OneToMany(mappedBy: 'recette', targetEntity: Ingredient::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['recette:read'])]
    private Collection $ingredients;

    #[ORM\ManyToMany(targetEntity: TagRecette::class, inversedBy: 'recettes')]
    #[Groups(['recette:read'])]
    private Collection $tags;

    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable('now');
    }

    #[ORM\PrePersist]
    public function touchDateCreation(): void
    {
        $this->dateCreation ??= new \DateTimeImmutable('now');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): static
    {
        $this->instructions = $instructions;

        return $this;
    }

    public function getTempsPreparation(): int
    {
        return $this->tempsPreparation;
    }

    public function setTempsPreparation(int $tempsPreparation): static
    {
        $this->tempsPreparation = $tempsPreparation;

        return $this;
    }

    public function getTempsCuisson(): ?int
    {
        return $this->tempsCuisson;
    }

    public function setTempsCuisson(?int $tempsCuisson): static
    {
        $this->tempsCuisson = $tempsCuisson;

        return $this;
    }

    public function getDifficulte(): string
    {
        return $this->difficulte;
    }

    public function setDifficulte(string $difficulte): static
    {
        $this->difficulte = $difficulte;

        return $this;
    }

    public function getNbPersonnes(): int
    {
        return $this->nbPersonnes;
    }

    public function setNbPersonnes(int $nbPersonnes): static
    {
        $this->nbPersonnes = $nbPersonnes;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function isPubliee(): bool
    {
        return $this->publiee;
    }

    public function setPubliee(bool $publiee): static
    {
        $this->publiee = $publiee;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getCategorie(): ?CategorieRecette
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieRecette $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;

        return $this;
    }

    /** @return Collection<int, Ingredient> */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setRecette($this);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        $this->ingredients->removeElement($ingredient);

        return $this;
    }

    /** @return Collection<int, TagRecette> */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(TagRecette $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(TagRecette $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
