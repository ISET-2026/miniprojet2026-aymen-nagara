<?php

namespace App\Tests\Api;

use App\Tests\BootstrapFixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RecetteApiTest extends WebTestCase
{
    use BootstrapFixturesTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown();
    }

    public function testGetCollectionAsJsonLd(): void
    {
        $client = static::createClient();
        self::resetSchemaAndFixtures(static::getContainer());

        $client->request('GET', '/api/recettes', [], [], ['HTTP_ACCEPT' => 'application/ld+json']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(
            'application/ld+json',
            (string) $client->getResponse()->headers->get('Content-Type')
        );
        $decoded = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($decoded);
        self::assertArrayHasKey('@context', $decoded);
        self::assertArrayHasKey('hydra:member', $decoded);
    }

    public function testPostValideReturns201(): void
    {
        $client = static::createClient();
        self::resetSchemaAndFixtures(static::getContainer());

        $payload = [
            'titre' => 'Recette API titre valide au moins 5 caracteres.',
            'description' => str_repeat('Description conforme avec plus de trente caracteres nécessaires. ', 2),
            'instructions' => 'Verser puis mélanger, cuire doucement sur feu modéré jusqu’à liaison.',
            'tempsPreparation' => 20,
            'difficulte' => 'facile',
            'nbPersonnes' => 4,
        ];

        $client->request(
            'POST',
            '/api/recettes',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/ld+json',
            ],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        $decoded = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($decoded);
        self::assertArrayHasKey('@id', $decoded);
        self::assertSame('Recette API titre valide au moins 5 caracteres.', $decoded['titre'] ?? null);
    }

    public function testPostSansTitreRetourneErreurDeValidation422(): void
    {
        $client = static::createClient();
        self::resetSchemaAndFixtures(static::getContainer());

        $payload = [
            'titre' => '',
            'description' => str_repeat('Une description bien assez longue pour valider sinon. ', 2),
            'instructions' => 'Instructions minimales nécessaires pour la recette de test erreur titre.',
            'tempsPreparation' => 10,
            'difficulte' => 'facile',
            'nbPersonnes' => 2,
        ];

        $client->request(
            'POST',
            '/api/recettes',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/ld+json',
            ],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(422);
    }
}
