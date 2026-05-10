<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
#[ORM\UniqueConstraint(name: 'uniq_user_pseudo', columns: ['pseudo'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est deja utilise.')]
#[UniqueEntity(fields: ['pseudo'], message: 'Ce pseudo est deja utilise.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    #[ORM\Column(length: 50)]
    private ?string $pseudo = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'auteur', targetEntity: Recette::class, orphanRemoval: false)]
    private Collection $recettes;

    public function __construct()
    {
        $this->recettes = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /** @return array<int, string> */
    public function getRoles(): array
    {
        $roles = array_unique($this->roles);
        sort($roles);

        return $roles ?: ['ROLE_USER'];
    }

    /** @param array<int, string> $roles */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): static
    {
        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /** @return Collection<int, Recette> */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function __toString(): string
    {
        return (string) ($this->pseudo ?: $this->email);
    }
}
