<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public const REF_ADMIN = 'user_admin';

    public const REF_CHEF = 'user_chef';

    private function hashPwd(User $user, string $plain): string
    {
        return password_hash($plain, \PASSWORD_DEFAULT);
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@recipehub.com')->setPseudo('admin')->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->hashPwd($admin, 'admin123'));
        $manager->persist($admin);
        $this->addReference(self::REF_ADMIN, $admin);

        $chef = new User();
        $chef->setEmail('chef@recipehub.com')->setPseudo('chef')->setRoles(['ROLE_CUISINIER']);
        $chef->setPassword($this->hashPwd($chef, 'chef123'));
        $manager->persist($chef);
        $this->addReference(self::REF_CHEF, $chef);

        $faker = \Faker\Factory::create('fr_FR');

        for ($i = 1; $i <= 5; ++$i) {
            $u = new User();
            $u->setEmail($faker->unique()->safeEmail())
                ->setPseudo($faker->firstName().$faker->randomDigitNotNull())
                ->setRoles(['ROLE_USER']);
            $u->setPassword($this->hashPwd($u, 'user123'.$i));
            $manager->persist($u);
            $this->addReference('user_faker_'.$i, $u);
        }

        $manager->flush();
    }
}
