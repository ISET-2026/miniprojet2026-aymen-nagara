<?php

namespace App\Service;

use App\Entity\Recette;
use App\Repository\CategorieRecetteRepository;
use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;

class RecetteAnalyser
{
    public function __construct(
        private readonly RecetteRepository $repo,
        private readonly IngredientRepository $ingredientRepository,
        private readonly CategorieRecetteRepository $categorieRecetteRepository,
    ) {
    }

    public function getTempsTotal(Recette $r): int
    {
        return $r->getTempsPreparation() + ($r->getTempsCuisson() ?? 0);
    }

    public function getTotalRecettesPubliees(): int
    {
        return (int) $this->repo->count(['publiee' => true]);
    }

    /** Catégorie (nom avec icône) => nombre de recettes publiées */
    public function getRecettesParCategorie(): array
    {
        $counts = $this->repo->countPublishedByCategories();
        $categories = $this->categorieRecetteRepository->findAll();
        $sorted = [];

        foreach ($categories as $categorie) {
            $id = $categorie->getId();
            if (null !== $id) {
                $icone = \trim((string) ($categorie->getIcone() ?? ''));
                $nom = \trim((string) $categorie->getNom());
                $label = '' !== $icone ? $icone.' '.$nom : $nom;
                $sorted[$label] = (int) ($counts[(string) $id] ?? 0);
            }
        }

        \arsort($sorted);

        return $sorted;
    }

    /** Moyenne d'ingrédients par recette (toutes recettes présentes). */
    public function getMoyenneIngredients(): float
    {
        $n = $this->repo->count([]);
        if (0 === $n) {
            return 0.0;
        }

        return \round($this->ingredientRepository->countAll() / $n, 2);
    }
}
