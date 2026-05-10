<?php

namespace App\DataFixtures;

use App\Entity\CategorieRecette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieRecetteFixtures extends Fixture
{
    public const REF_CAT_ENTREE = 'cat_entree';

    public const REF_CAT_PLAT = 'cat_plat';

    public const REF_CAT_DESSERT = 'cat_dessert';

    public const REF_CAT_BOISSON = 'cat_boisson';

    public const REF_CAT_SNACK = 'cat_snack';

    public const REF_CAT_SOUPE = 'cat_soupe';

    public function load(ObjectManager $manager): void
    {
        $data = [
            [self::REF_CAT_ENTREE, 'Entree', '🥗', 'Entrees fraiches et appetissantes'],
            [self::REF_CAT_PLAT, 'Plat', '🍝', 'Plats principaux pour le repas'],
            [self::REF_CAT_DESSERT, 'Dessert', '🍰', 'Douceurs pour finir'],
            [self::REF_CAT_BOISSON, 'Boisson', '🥤', 'Breuvages varies'],
            [self::REF_CAT_SNACK, 'Snack', '🍕', 'Pour grignoter vite'],
            [self::REF_CAT_SOUPE, 'Soupe', '🥣', 'Soupes et potsages'],
        ];

        foreach ($data as [$ref, $nom, $icone, $desc]) {
            $c = (new CategorieRecette())->setNom($nom)->setIcone($icone)->setDescription($desc);
            $manager->persist($c);
            $this->addReference($ref, $c);
        }

        $manager->flush();
    }
}
