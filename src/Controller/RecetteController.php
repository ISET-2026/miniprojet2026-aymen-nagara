<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Entity\User;
use App\Form\RecetteFilterType;
use App\Form\RecetteType;
use App\Repository\CategorieRecetteRepository;
use App\Repository\RecetteRepository;
use App\Service\FavoriteRecipesService;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RecetteController extends AbstractController
{
    #[Route('/recettes', name: 'app_recette_index', methods: ['GET'])]
    public function index(
        Request $request,
        RecetteRepository $recetteRepository,
        PaginatorInterface $paginator,
    ): Response {
        $form = $this->createForm(RecetteFilterType::class);
        $form->handleRequest($request);

        /** @phpstan-ignore-next-line Symfony union */
        $titreRaw = $form->get('titre')->getData();
        /** @phpstan-ignore-next-next-line doctrine */
        $cat = $form->get('categorie')->getData();
        /** @phpstan-ignore-next-next-line Symfony form */
        $diffRaw = $form->get('difficulte')->getData();
        /** @phpstan-ignore-next-next-line doctrine */
        $tag = $form->get('tag')->getData();

        $titre = \is_scalar($titreRaw) ? (string) $titreRaw : null;
        $diff = \is_scalar($diffRaw) ? (string) $diffRaw : null;

        $qb = $recetteRepository->createFilteredQueryBuilder(
            $titre ?: null,
            $cat ?: null,
            $diff ?: null,
            $tag ?: null,
            true,
        );

        $pagination = $paginator->paginate($qb, $request->query->getInt('page', 1), 9, [
            'defaultSortFieldName' => 'r.dateCreation',
            'defaultSortDirection' => 'desc',
        ]);

        return $this->render('recette/index.html.twig', [
            'pagination' => $pagination,
            'filterForm' => $form->createView(),
            'resultsTotal' => $pagination->getTotalItemCount(),
        ]);
    }

    #[Route('/recettes/{id}', name: 'app_recette_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Recette $recette): Response
    {
        /** @phpstan-ignore-next-next-line doctrine */
        $current = $this->getUser();
        if (!$recette->isPubliee()
            && !($current instanceof User && (\in_array('ROLE_ADMIN', $current->getRoles(), true) || $current->getId() === $recette->getAuteur()?->getId()))
        ) {
            throw $this->createNotFoundException();
        }

        return $this->render('recette/show.html.twig', [
            'recette' => $recette,
        ]);
    }

    #[Route('/recettes/nouvelle', name: 'app_recette_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CUISINIER')]
    public function nouveau(
        Request $request,
        EntityManagerInterface $em,
        FileUploader $fileUploader,
        CategorieRecetteRepository $categorieRepo,
    ): Response {
        $recette = new Recette();

        try {
            if (null === $recette->getCategorie() && [] !== ($first = $categorieRepo->findAll())) {
                $recette->setCategorie($first[0]);
            }
        } catch (\Throwable) {
        }

        $recette->setAuteur($this->getTypedUser());

        return $this->handleRecettePersist($request, $recette, $em, $fileUploader, false);
    }

    #[Route('/recettes/{id}/modifier', name: 'app_recette_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CUISINIER')]
    public function modifier(
        Recette $recette,
        Request $request,
        EntityManagerInterface $em,
        FileUploader $fileUploader,
    ): Response {
        $this->denyUnlessAuthorOrAdmin($recette);

        return $this->handleRecettePersist($request, $recette, $em, $fileUploader, true);
    }

    #[Route('/recettes/{id}/supprimer', name: 'app_recette_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_CUISINIER')]
    public function delete(Recette $recette, Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $this->denyUnlessAuthorOrAdmin($recette);

        if ($this->isCsrfTokenValid('delete'.$recette->getId(), (string) $request->request->get('_token'))) {
            $img = $recette->getImageName();
            if ($img) {
                $fileUploader->remove($img);
            }

            $em->remove($recette);
            $em->flush();
            $this->addFlash('success', 'La recette a été supprimée.');
        }

        return $this->redirectToRoute('app_recette_index');
    }

    #[Route('/recettes/{id}/favorite', name: 'app_recette_favorite', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function addFavorite(Recette $recette, Request $request, FavoriteRecipesService $favorites): Response
    {
        $id = $recette->getId();
        $token = $request->request->get('_token');
        if (null === $id || !\is_string($token) || !$this->isCsrfTokenValid('favorite'.$id, $token)) {
            throw $this->createAccessDeniedException();
        }

        $favorites->add($id);
        $this->addFlash('success', 'Recette ajoutée aux favoris.');

        return $this->redirectToRoute('app_recette_show', ['id' => $id]);
    }

    /** @param bool $isEdit true lors d'une mise à jour (suppression ancienne image possible) */
    private function handleRecettePersist(
        Request $request,
        Recette $recette,
        EntityManagerInterface $em,
        FileUploader $fileUploader,
        bool $isEdit,
    ): Response {
        $form = $this->createForm(RecetteType::class, $recette);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @phpstan-ignore-next-line Symfony upload */
            $uploaded = $form->get('imageFile')->getData();
            if ($uploaded) {
                if ($isEdit && $recette->getImageName()) {
                    $fileUploader->remove((string) $recette->getImageName());
                }
                $name = $fileUploader->upload($uploaded);
                $recette->setImageName($name);
            }

            $recette->setAuteur($recette->getAuteur() ?? $this->getTypedUser());
            $em->persist($recette);
            $em->flush();

            $msg = $isEdit ? 'Recette mise à jour.' : 'Recette créée.';
            $this->addFlash('success', $msg);

            return $this->redirectToRoute('app_recette_show', ['id' => (int) $recette->getId()]);
        }

        return $this->render('recette/form.html.twig', [
            'form' => $form,
            'recette' => $recette,
            'edition' => $isEdit || null !== $recette->getId(),
        ]);
    }

    private function denyUnlessAuthorOrAdmin(Recette $recette): void
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return;
        }

        $user = $this->getTypedUser();
        $auteur = $recette->getAuteur();
        if (null !== $user->getId() && null !== $auteur && $user->getId() === $auteur->getId()) {
            return;
        }

        throw $this->createAccessDeniedException('Seul un administrateur ou l’auteur peut modifier cette recette.');
    }

    /** @todo remove assert when generics on security */
    private function getTypedUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
