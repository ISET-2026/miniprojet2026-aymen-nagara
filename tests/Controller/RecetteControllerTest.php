<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\CategorieRecetteRepository;
use App\Repository\UserRepository;
use App\Tests\BootstrapFixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecetteControllerTest extends WebTestCase
{
    use BootstrapFixturesTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown();
    }

    public function testListeRecettesStatus200EtCartes(): void
    {
        $client = static::createClient();
        self::resetSchemaAndFixtures(static::getContainer());

        $client->request('GET', '/recettes');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.card');
    }

    public function testCreationInterditeSansAuth(): void
    {
        $client = static::createClient();
        self::resetSchemaAndFixtures(static::getContainer());

        $client->request('GET', '/recettes/nouvelle');
        self::assertResponseStatusCodeSame(302);
        self::assertTrue($client->getResponse()->isRedirect());
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testCreationViaFormulaireEtFlash(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        self::resetSchemaAndFixtures($container);

        $chef = $container->get(UserRepository::class)->findOneBy(['email' => 'chef@recipehub.com']);
        self::assertInstanceOf(User::class, $chef);
        $client->loginUser($chef);

        $categorie = $container->get(CategorieRecetteRepository::class)->findOneBy([], ['id' => 'ASC']);
        self::assertNotNull($categorie);
        $catId = (string) $categorie->getId();

        $crawler = $client->request('GET', '/recettes/nouvelle');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form([
            'recette[titre]' => 'Création PHPUnit titre valide',
            'recette[description]' => str_repeat('Une description longue minimale conforme aux contraintes. ', 2),
            'recette[instructions]' => 'Étapes de la recette de test PHPUnit pour la création.',
            'recette[tempsPreparation]' => '25',
            'recette[tempsCuisson]' => '',
            'recette[difficulte]' => 'facile',
            'recette[nbPersonnes]' => '4',
            'recette[categorie]' => $catId,
        ]);

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-success');
        self::assertSelectorTextContains('.alert-success', 'Recette créée.');
    }
}
