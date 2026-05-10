<?php

namespace App\Controller;

use App\Entity\CategorieRecette;
use App\Form\CategorieRecetteFormType;
use App\Repository\CategorieRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/categories')]
class CategorieRecetteAdminController extends AbstractController
{
    #[Route('', name: 'app_categorie_index', methods: ['GET'])]
    public function index(CategorieRecetteRepository $repo): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $repo->findAll(),
        ]);
    }

    #[Route('/nouvelle', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function nouvelle(Request $request, EntityManagerInterface $em): Response
    {
        $c = new CategorieRecette();
        $form = $this->createForm(CategorieRecetteFormType::class, $c);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($c);
            $em->flush();
            $this->addFlash('success', 'Catégorie créée.');

            return $this->redirectToRoute('app_categorie_index');
        }

        return $this->render('categorie/form.html.twig', [
            'form' => $form,
        ]);
    }
}
