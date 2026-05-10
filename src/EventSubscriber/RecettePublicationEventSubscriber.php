<?php

namespace App\EventSubscriber;

use App\Entity\Recette;
use App\Mail\RecettePublicationMailSender;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;

final class RecettePublicationEventSubscriber implements EventSubscriber
{
    /** @var list<int> */
    private array $idsDevenuesPubliees = [];

    public function __construct(
        private readonly RecettePublicationMailSender $sender,
        private readonly ManagerRegistry $registry,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist => 'postPersist',
            Events::onFlush => 'onFlush',
            Events::postFlush => 'postFlush',
        ];
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Recette || !$entity->isPubliee()) {
            return;
        }

        $this->sender->sendPublicationNotification($entity);
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $this->idsDevenuesPubliees = [];

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Recette || null === $entity->getId()) {
                continue;
            }

            $chg = $uow->getEntityChangeSet($entity);
            if (!isset($chg['publiee'])) {
                continue;
            }

            /** @phpstan-ignore-next-line doctrine */
            [$ancien, $nouveau] = $chg['publiee'];
            if (!$ancien && $nouveau && $entity->isPubliee()) {
                $this->idsDevenuesPubliees[] = (int) $entity->getId();
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if ([] === $this->idsDevenuesPubliees) {
            return;
        }

        /** @phpstan-ignore-next-line doctrine */
        $repo = $this->registry->getRepository(Recette::class);
        foreach ($this->idsDevenuesPubliees as $id) {
            $r = $repo->find($id);
            if ($r instanceof Recette && $r->isPubliee()) {
                $this->sender->sendPublicationNotification($r);
            }
        }

        $this->idsDevenuesPubliees = [];
    }
}
