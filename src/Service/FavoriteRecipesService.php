<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class FavoriteRecipesService
{
    public const SESSION_KEY = 'recipehub_favorites';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /** @param positive-int $id */
    public function add(int $id): void
    {
        $session = $this->requestStack->getSession();
        /** @var list<int> */
        $ids = $session->get(self::SESSION_KEY, []);
        if (!\in_array($id, $ids, true)) {
            $ids[] = $id;
        }

        $session->set(self::SESSION_KEY, $ids);
    }

    /** @return list<int> */
    public function getFavoriteIds(): array
    {
        $session = $this->requestStack->getSession();
        /** @var list<int>|null $ids */
        $ids = $session->get(self::SESSION_KEY);

        return \array_values(\array_unique(\array_map(static fn ($v) => (int) $v, \is_array($ids) ? $ids : [])));
    }

    public function remove(int $id): void
    {
        $session = $this->requestStack->getSession();
        $ids = \array_values(\array_diff($this->getFavoriteIds(), [$id]));
        $session->set(self::SESSION_KEY, $ids);
    }

    /** @param positive-int $id */
    public function contains(int $id): bool
    {
        return \in_array($id, $this->getFavoriteIds(), true);
    }
}
