<?php

namespace App\Twig;

use App\Service\FavoriteRecipesService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class RecipeHubExtension extends AbstractExtension
{
    public function __construct(
        private readonly FavoriteRecipesService $favoritesService,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago', [$this, 'filterTimeAgo']),
            new TwigFilter('cooking_time_format', [$this, 'filterCookingFormat']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('difficulty_stars', [$this, 'difficultyStars']),
            new TwigFunction('favorite_recipes_count', [$this, 'favoriteRecipesCount']),
        ];
    }

    public function difficultyStars(?string $difficulte): string
    {
        return match ($difficulte) {
            'facile' => '⭐',
            'moyen' => '⭐⭐',
            'difficile' => '⭐⭐⭐',
            default => '—',
        };
    }

    public function favoriteRecipesCount(): int
    {
        return \count($this->favoritesService->getFavoriteIds());
    }

    public function filterTimeAgo(?\DateTimeInterface $dt): string
    {
        if (!$dt instanceof \DateTimeInterface) {
            return '—';
        }

        $now = new \DateTimeImmutable('now');

        /** @phpstan-ignore-next-ignore */
        $diff = \DateTimeImmutable::createFromInterface($dt)->diff($now);

        if (false !== $diff->invert) {
            return 'à venir';
        }

        if ($diff->y > 0) {
            return 'il y a '.$diff->y.' an'.($diff->y > 1 ? 's' : '');
        }
        if ($diff->m > 0) {
            return 'il y a '.$diff->m.' mois';
        }
        if ($diff->d > 0) {
            return 'il y a '.$diff->d.' jour'.($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0 || $diff->i > 0 || $diff->s > 0) {
            return 'il y a quelques minutes';
        }

        return 'à l\'instant';
    }

    public function filterCookingFormat(mixed $minutes): string
    {
        if (!\is_numeric($minutes)) {
            return '—';
        }

        /** @phpstan-ignore-next-ignore */
        $m = max(0, (int) $minutes);

        return match (true) {
            0 >= $m => '—',
            $m < 60 => $m.'min',
            0 === $m % 60 => intdiv($m, 60).'h',
            default => intdiv($m, 60).'h'.($m % 60),
        };
    }
}
