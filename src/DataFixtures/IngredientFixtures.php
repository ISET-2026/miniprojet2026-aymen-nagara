<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IngredientFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [RecetteFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 1; $i <= 20; ++$i) {
            $recette = $this->getReference('recette_'.$i, Recette::class);
            if (!$recette instanceof Recette) {
                continue;
            }

            $n = rand(3, 8);
            for ($k = 0; $k < $n; ++$k) {
                $quantite = rand(50, 500).'g';
                if (rand(0, 1) === 1) {
                    $quantite = rand(5, 30).' ml';
                }

                $ingredient = (new Ingredient())
                    ->setNom(ucfirst(implode(' ', $faker->words(rand(2, 3)))).'-'.$i.'-'.$k)
                    ->setQuantite($quantite)
                    ->setRecette($recette);
                $manager->persist($ingredient);
            }
        }

        $manager->flush();
    }
}
