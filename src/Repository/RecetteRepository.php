<?php

namespace App\Repository;

use App\Entity\CategorieRecette;
use App\Entity\Recette;
use App\Entity\TagRecette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recette>
 */
class RecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recette::class);
    }

    public function createFilteredQueryBuilder(
        ?string $titre,
        ?CategorieRecette $cat,
        ?string $diff,
        ?TagRecette $tag,
        bool $onlyPublished = false,
    ): \Doctrine\ORM\QueryBuilder {
        $qb = $this->createQueryBuilder('r');

        if ($onlyPublished) {
            $qb->andWhere('r.publiee = true');
        }

        if ($titre) {
            $qb->andWhere('r.titre LIKE :titre')
                ->setParameter('titre', '%'.$titre.'%');
        }
        if ($cat) {
            $qb->andWhere('r.categorie = :cat')
                ->setParameter('cat', $cat);
        }
        if ($diff) {
            $qb->andWhere('r.difficulte = :diff')
                ->setParameter('diff', $diff);
        }
        if ($tag) {
            $qb->innerJoin('r.tags', 't')
                ->andWhere('t = :tag')
                ->setParameter('tag', $tag);
        }

        return $qb->orderBy('r.dateCreation', 'DESC');
    }

    /**
     * @param list<int> $orderedIds
     *
     * @return Recette[]
     */
    public function findPublishedByOrderedIds(array $orderedIds): array
    {
        if ([] === $orderedIds) {
            return [];
        }

        /** @var Recette[] $rows */
        $rows = $this->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $orderedIds)
            ->andWhere('r.publiee = true')
            ->getQuery()
            ->getResult();

        $byId = [];
        foreach ($rows as $row) {
            if (null !== $row->getId()) {
                $byId[$row->getId()] = $row;
            }
        }

        $ordered = [];
        foreach ($orderedIds as $id) {
            if (isset($byId[$id])) {
                $ordered[] = $byId[$id];
            }
        }

        return $ordered;
    }

    /** @return array<int, int> auteur ID => nombre de recettes publiées */
    public function countPublishedRecipesGroupedByAuthorId(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.auteur) AS uid, COUNT(r.id) AS cnt')
            ->andWhere('r.publiee = true')
            ->groupBy('r.auteur')
            ->getQuery()
            ->getArrayResult();

        $assoc = [];
        foreach ($rows as $row) {
            $assoc[(int) ($row['uid'] ?? 0)] = (int) ($row['cnt'] ?? 0);
        }

        return $assoc;
    }

    /**
     * @return Recette[]
     */
    public function findLastPublished(int $limit = 3): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.publiee = true')
            ->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, int> category id => recipe count for published recipes
     */
    public function countPublishedByCategories(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.categorie) AS cid, COUNT(r.id) AS cnt')
            ->andWhere('r.publiee = true')
            ->groupBy('r.categorie')
            ->getQuery()
            ->getArrayResult();

        $assoc = [];
        foreach ($rows as $row) {
            $assoc[(string) ($row['cid'] ?? '')] = (int) ($row['cnt'] ?? 0);
        }

        return $assoc;
    }

    /**
     * @return array<string, int>
     */
    public function countPublishedByDifficulty(): array
    {
        $rows = $this->createQueryBuilder('r')
            ->select('r.difficulte AS df, COUNT(r.id) AS cnt')
            ->andWhere('r.publiee = true')
            ->groupBy('r.difficulte')
            ->getQuery()
            ->getArrayResult();

        $assoc = [];
        foreach ($rows as $row) {
            $assoc[(string) ($row['df'] ?? '')] = (int) ($row['cnt'] ?? 0);
        }

        return $assoc;
    }

}
