<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\User;
use App\Form\IngredientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_CUISINIER')]
class IngredientController extends AbstractController
{
    #[Route('/recettes/{id}/ingredients/nouveau', name: 'app_ingredient_new', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function nouveau(Recette $recette, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyUnlessAuthorOrAdmin($recette);

        $ingredient = new Ingredient();
        $ingredient->setRecette($recette);

        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ingredient);
            $em->flush();
            $this->addFlash('success', 'Ingrédient ajouté.');

            return $this->redirectToRoute('app_recette_show', ['id' => (int) $recette->getId()]);
        }

        return $this->render('ingredient/form.html.twig', [
            'form' => $form,
            'recette' => $recette,
        ]);
    }

    #[Route('/ingredients/{id}/supprimer', name: 'app_ingredient_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Ingredient $ingredient, Request $request, EntityManagerInterface $em): Response
    {
        $recette = $ingredient->getRecette();
        if (!$recette instanceof Recette || null === $recette->getId()) {
            throw $this->createNotFoundException();
        }

        $this->denyUnlessAuthorOrAdmin($recette);

        if ($this->isCsrfTokenValid('delete_ingredient'.$ingredient->getId(), (string) $request->request->get('_token'))) {
            $em->remove($ingredient);
            $em->flush();
            $this->addFlash('success', 'Ingrédient retiré.');
        }

        return $this->redirectToRoute('app_recette_show', ['id' => (int) $recette->getId()]);
    }

    private function denyUnlessAuthorOrAdmin(Recette $recette): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->getUser();
        $auteur = $recette->getAuteur();
        if ($user instanceof User && null !== $user->getId() && null !== $auteur && $user->getId() === $auteur->getId()) {
            return;
        }

        throw $this->createAccessDeniedException();
    }
}
