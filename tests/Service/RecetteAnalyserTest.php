<?php

namespace App\Tests\Service;

use App\Entity\CategorieRecette;
use App\Entity\Recette;
use App\Repository\CategorieRecetteRepository;
use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;
use App\Service\RecetteAnalyser;
use PHPUnit\Framework\TestCase;

final class RecetteAnalyserTest extends TestCase
{
    public function testGetTempsTotalSommePrepEtCuisson(): void
    {
        $repo = $this->createStub(RecetteRepository::class);
        $ing = $this->createStub(IngredientRepository::class);
        $cats = $this->createStub(CategorieRecetteRepository::class);

        $analyser = new RecetteAnalyser($repo, $ing, $cats);

        $r = (new Recette())->setTempsPreparation(12)->setTempsCuisson(8);
        self::assertSame(20, $analyser->getTempsTotal($r));

        $r2 = (new Recette())->setTempsPreparation(30)->setTempsCuisson(null);
        self::assertSame(30, $analyser->getTempsTotal($r2));
    }

    public function testGetTotalRecettesPublieesDelègueAuRepository(): void
    {
        $repo = $this->createMock(RecetteRepository::class);
        $repo->expects(self::once())->method('count')->with(['publiee' => true])->willReturn(7);

        $analyser = new RecetteAnalyser(
            $repo,
            $this->createStub(IngredientRepository::class),
            $this->createStub(CategorieRecetteRepository::class)
        );

        self::assertSame(7, $analyser->getTotalRecettesPubliees());
    }

    public function testGetRecettesParCategorieConstruitEtTrieLabels(): void
    {
        $repo = $this->createStub(RecetteRepository::class);
        $repo->method('countPublishedByCategories')->willReturn([
            '10' => 3,
            '5' => 1,
        ]);

        $c10 = $this->createMock(CategorieRecette::class);
        $c10->method('getId')->willReturn(10);
        $c10->method('getNom')->willReturn('Desserts');
        $c10->method('getIcone')->willReturn('🍰');

        $c5 = $this->createMock(CategorieRecette::class);
        $c5->method('getId')->willReturn(5);
        $c5->method('getNom')->willReturn('Viandes');
        $c5->method('getIcone')->willReturn('');

        $catRepo = $this->createStub(CategorieRecetteRepository::class);
        $catRepo->method('findAll')->willReturn([$c10, $c5]);

        $analyser = new RecetteAnalyser(
            $repo,
            $this->createStub(IngredientRepository::class),
            $catRepo
        );

        self::assertSame(
            ['🍰 Desserts' => 3, 'Viandes' => 1],
            $analyser->getRecettesParCategorie()
        );
    }

    public function testGetMoyenneIngredientsDivisionArrondieDeuxDecimals(): void
    {
        $repo = $this->createStub(RecetteRepository::class);
        $repo->method('count')->willReturnCallback(function ($criteria) {
            self::assertSame([], $criteria);

            return 10;
        });

        $ing = $this->createStub(IngredientRepository::class);
        $ing->method('countAll')->willReturn(25);

        $analyser = new RecetteAnalyser(
            $repo,
            $ing,
            $this->createStub(CategorieRecetteRepository::class)
        );

        self::assertSame(2.5, $analyser->getMoyenneIngredients());
    }

    public function testGetMoyenneIngredientsSansRecetteRetourneZeroSansAppelerIngredients(): void
    {
        $repo = $this->createMock(RecetteRepository::class);
        $repo->method('count')->willReturn(0);

        $ing = $this->createMock(IngredientRepository::class);
        $ing->expects(self::never())->method('countAll');

        $analyser = new RecetteAnalyser(
            $repo,
            $ing,
            $this->createStub(CategorieRecetteRepository::class)
        );

        self::assertSame(0.0, $analyser->getMoyenneIngredients());
    }
}
