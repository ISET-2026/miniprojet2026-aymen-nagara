<?php

namespace App\DataFixtures;

use App\Entity\CategorieRecette;
use App\Entity\Recette;
use App\Entity\TagRecette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RecetteFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            CategorieRecetteFixtures::class,
            TagRecetteFixtures::class,
            UserFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $catRefs = [
            CategorieRecetteFixtures::REF_CAT_ENTREE,
            CategorieRecetteFixtures::REF_CAT_PLAT,
            CategorieRecetteFixtures::REF_CAT_DESSERT,
            CategorieRecetteFixtures::REF_CAT_BOISSON,
            CategorieRecetteFixtures::REF_CAT_SNACK,
            CategorieRecetteFixtures::REF_CAT_SOUPE,
        ];

        $tagRefs = [
            TagRecetteFixtures::REF_TAG_VEGETARIEN,
            'tag_vegan',
            'tag_sans_gluten',
            'tag_bio',
            'tag_rapide',
            'tag_familial',
            'tag_festif',
            'tag_economique',
        ];

        $authors = [
            UserFixtures::REF_ADMIN,
            UserFixtures::REF_CHEF,
            'user_faker_1',
            'user_faker_2',
            'user_faker_3',
            'user_faker_4',
            'user_faker_5',
        ];

        for ($i = 1; $i <= 20; ++$i) {
            $cat = $this->getReference($faker->randomElement($catRefs), CategorieRecette::class);
            $chef = $this->getReference($faker->randomElement($authors), User::class);

            $tit = ucfirst(implode(' ', $faker->words(4)));
            $paragraphsDesc = $faker->paragraphs(8, false);
            $desc = implode("\n\n", $paragraphsDesc);
            if (strlen($desc) < 30) {
                $desc .= '. '.implode(' ', $faker->sentences(rand(3, 5)));
            }

            $r = new Recette();
            $r->setTitre($tit);
            $r->setDescription($desc);
            $r->setInstructions(implode("\n\n", $faker->paragraphs(6, false)));
            $r->setTempsPreparation(rand(5, 90));
            $r->setTempsCuisson(rand(0, 1) === 1 ? rand(0, 180) : null);
            $r->setDifficulte($faker->randomElement(['facile', 'moyen', 'difficile']));
            $r->setNbPersonnes(rand(1, 8));
            $r->setPubliee($faker->boolean(82));
            $r->setCategorie($cat);
            $r->setAuteur($chef);

            $shuffled = $tagRefs;
            shuffle($shuffled);
            $nbTags = rand(1, 4);
            for ($ti = 0; $ti < $nbTags; ++$ti) {
                $tagRef = $shuffled[$ti];
                $tag = $this->getReference($tagRef, TagRecette::class);
                $r->addTag($tag);
            }

            $manager->persist($r);
            $this->addReference('recette_'.$i, $r);
        }

        $manager->flush();
    }
}
