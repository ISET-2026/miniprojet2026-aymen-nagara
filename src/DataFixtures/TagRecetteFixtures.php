<?php

namespace App\DataFixtures;

use App\Entity\TagRecette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagRecetteFixtures extends Fixture
{
    public const REF_TAG_VEGETARIEN = 'tag_vegetarien';

    public function load(ObjectManager $manager): void
    {
        $tags = [
            [self::REF_TAG_VEGETARIEN, 'Vegetarien', '#2ecc71'],
            ['tag_vegan', 'Vegan', '#27ae60'],
            ['tag_sans_gluten', 'Sans Gluten', '#e67e22'],
            ['tag_bio', 'Bio', '#1abc9c'],
            ['tag_rapide', 'Rapide', '#3498db'],
            ['tag_familial', 'Familial', '#9b59b6'],
            ['tag_festif', 'Festif', '#e74c3c'],
            ['tag_economique', 'Economique', '#95a5a6'],
        ];

        foreach ($tags as $row) {
            [$ref, $nom, $couleur] = $row;
            $t = (new TagRecette())->setNom($nom)->setCouleur($couleur);
            $manager->persist($t);
            $this->addReference($ref, $t);
        }

        $manager->flush();
    }
}
