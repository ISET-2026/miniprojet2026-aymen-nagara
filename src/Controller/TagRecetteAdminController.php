<?php

namespace App\Controller;

use App\Entity\TagRecette;
use App\Form\TagRecetteFormType;
use App\Repository\TagRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/tags')]
class TagRecetteAdminController extends AbstractController
{
    #[Route('', name: 'app_tag_index', methods: ['GET'])]
    public function index(TagRecetteRepository $repository): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $repository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'app_tag_new', methods: ['GET', 'POST'])]
    public function nouvelle(Request $request, EntityManagerInterface $em): Response
    {
        $tag = new TagRecette();
        $form = $this->createForm(TagRecetteFormType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tag);
            $em->flush();
            $this->addFlash('success', 'Tag créé.');

            return $this->redirectToRoute('app_tag_index');
        }

        return $this->render('tag/form.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/supprimer', name: 'app_tag_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(TagRecette $tag, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_tag'.$tag->getId(), (string) $request->request->get('_token'))) {
            $em->remove($tag);
            $em->flush();
            $this->addFlash('success', 'Tag supprimé.');
        }

        return $this->redirectToRoute('app_tag_index');
    }
}
