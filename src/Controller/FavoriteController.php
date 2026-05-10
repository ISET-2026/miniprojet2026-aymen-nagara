<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use App\Service\FavoriteRecipesService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/mes-favoris')]
class FavoriteController extends AbstractController
{
    #[Route('', name: 'app_favoris_index', methods: ['GET'])]
    public function index(
        Request $request,
        FavoriteRecipesService $favorites,
        RecetteRepository $recettes,
        PaginatorInterface $paginator,
    ): Response {
        $ids = $favorites->getFavoriteIds();

        /** @phpstan-ignore-next-next-line Paginator pagination */
        $results = [] === $ids ? [] : $recettes->findPublishedByOrderedIds($ids);
        /** @phpstan-ignore-next-next-line Paginator pagination */
        $pagination = $paginator->paginate($results, $request->query->getInt('page', 1), 6);

        return $this->render('favorite/index.html.twig', ['pagination' => $pagination]);
    }

    #[Route('/retirer/{id}', name: 'app_favoris_retirer', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function retirer(Request $request, int $id, FavoriteRecipesService $favorites): Response
    {
        if (!$this->isCsrfTokenValid('favorite_remove'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $favorites->remove($id);
        $this->addFlash('success', 'Retiré des favoris.');

        return $this->redirectToRoute('app_favoris_index');
    }
}
