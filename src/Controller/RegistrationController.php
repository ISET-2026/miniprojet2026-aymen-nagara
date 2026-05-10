<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    private const ADMIN_INVITE_CODE = 'RECIPEHUB-ADMIN-2026';

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accountType = (string) $form->get('accountType')->getData();
            $invitationCode = trim((string) $form->get('invitationCode')->getData());

            if ('admin' === $accountType) {
                if (self::ADMIN_INVITE_CODE !== $invitationCode) {
                    $form->get('invitationCode')->addError(new FormError('Code d\'invitation invalide.'));

                    return $this->render('registration/register.html.twig', ['form' => $form]);
                }

                $user->setRoles(['ROLE_ADMIN']);
            } elseif ('chef' === $accountType) {
                $user->setRoles(['ROLE_CUISINIER']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            /** @phpstan-ignore-next-line Symfony form */
            $plain = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, \is_string($plain) ? $plain : ''));

            $entityManager->persist($user);
            try {
                $entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                // Safety net for race conditions or missed pre-validation.
                $repo = $entityManager->getRepository(User::class);
                if (null !== $repo->findOneBy(['email' => $user->getEmail()])) {
                    $form->get('email')->addError(new FormError('Cet email est deja utilise.'));
                }
                if (null !== $repo->findOneBy(['pseudo' => $user->getPseudo()])) {
                    $form->get('pseudo')->addError(new FormError('Ce pseudo est deja utilise.'));
                }

                return $this->render('registration/register.html.twig', ['form' => $form]);
            }

            $this->addFlash('success', 'Votre compte a été créé. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', ['form' => $form]);
    }
}
