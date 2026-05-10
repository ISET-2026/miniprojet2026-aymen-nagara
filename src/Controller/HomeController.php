<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use App\Service\RecetteAnalyser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecetteAnalyser $analyser, RecetteRepository $recetteRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'stats' => [
                'publiees' => $analyser->getTotalRecettesPubliees(),
                'temps_total_moyenne' => '—',
                'par_cat' => $analyser->getRecettesParCategorie(),
                'moy_ingredients' => $analyser->getMoyenneIngredients(),
            ],
            'recettes_recentes' => $recetteRepository->findLastPublished(3),
        ]);
    }
}
