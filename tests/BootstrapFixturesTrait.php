<?php

namespace App\Tests;

use App\DataFixtures\CategorieRecetteFixtures;
use App\DataFixtures\IngredientFixtures;
use App\DataFixtures\RecetteFixtures;
use App\DataFixtures\TagRecetteFixtures;
use App\DataFixtures\UserFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait BootstrapFixturesTrait
{
    protected static function resetSchemaAndFixtures(ContainerInterface $container): void
    {
        $em = $container->get(EntityManagerInterface::class);
        $meta = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);
        $tool->dropSchema($meta);
        $tool->createSchema($meta);

        $loader = new Loader();
        $loader->addFixture(new CategorieRecetteFixtures());
        $loader->addFixture(new TagRecetteFixtures());
        $loader->addFixture(new UserFixtures());
        $loader->addFixture(new RecetteFixtures());
        $loader->addFixture(new IngredientFixtures());

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute($loader->getFixtures(), false);
    }
}
