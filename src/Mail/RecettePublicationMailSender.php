<?php

namespace App\Mail;

use App\Entity\Recette;
use App\Service\RecetteAnalyser;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;

class RecettePublicationMailSender
{
    public function __construct(
        private readonly MailerInterface $mailer,
        #[Autowire('%recipehub.notification_email%')] private readonly string $notificationEmail,
        private readonly RecetteAnalyser $recetteAnalyser,
    ) {
    }

    public function sendPublicationNotification(Recette $recette): void
    {
        try {
            $email = new TemplatedEmail();
            $email->from('noreply@recipehub.com')
                ->to($this->notificationEmail)
                ->subject('🍽️ Nouvelle recette : '.$recette->getTitre())
                ->htmlTemplate('emails/nouvelle_recette.html.twig')
                ->context([
                    'recette' => $recette,
                    'auteurPseudo' => $recette->getAuteur()?->__toString() ?? '—',
                    'temps_total' => $this->recetteAnalyser->getTempsTotal($recette),
                ]);

            $this->mailer->send($email);
        } catch (\Throwable) {
            // null://mailer ou environnement incomplet — ne doit pas faire échouer la persistance
        }
    }
}
