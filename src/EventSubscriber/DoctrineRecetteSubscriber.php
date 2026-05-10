<?php

namespace App\EventSubscriber;

use App\Entity\CategorieRecette;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;

final readonly class DoctrineRecetteSubscriber implements EventSubscriber
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [Events::prePersist];
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof Recette) {
            return;
        }

        if (null === $entity->getAuteur()) {
            /** @phpstan-ignore-next-ignore */
            $user = $this->registry->getRepository(User::class)->findOneBy([], ['id' => 'ASC']);
            if ($user instanceof User) {
                $entity->setAuteur($user);
            }
        }

        if (null === $entity->getCategorie()) {
            /** @phpstan-ignore-next-ignore */
            $cat = $this->registry->getRepository(CategorieRecette::class)->findOneBy([], ['id' => 'ASC']);
            if ($cat instanceof CategorieRecette) {
                $entity->setCategorie($cat);
            }
        }
    }
}
