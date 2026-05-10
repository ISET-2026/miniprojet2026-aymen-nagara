<?php

namespace App\Command;

use App\Entity\CategorieRecette;
use App\Entity\Recette;
use App\Entity\User;
use App\Repository\CategorieRecetteRepository;
use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: "app:recipehub:stats",
    description: "Affiche les statistiques de la plateforme de recettes",
)]
final class RecipeHubStatsCommand extends Command
{
    public function __construct(
        private readonly RecetteRepository $recetteRepository,
        private readonly IngredientRepository $ingredientRepository,
        private readonly CategorieRecetteRepository $categorieRepository,
        private readonly ManagerRegistry $registry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption("detail", null, InputOption::VALUE_NONE, "Repartition detaillee par categorie")
            ->addOption("top", null, InputOption::VALUE_OPTIONAL, "Top N temps total maximal", "");
    }

    private function tempsTotal(Recette $r): int
    {
        return $r->getTempsPreparation() + (int) ($r->getTempsCuisson() ?? 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pub = $this->recetteRepository->count(["publiee" => true]);
        $brouillon = $this->recetteRepository->count(["publiee" => false]);
        $ingredientTotal = $this->ingredientRepository->countAll();

        $avgPrepScalar = $this->recetteRepository->createQueryBuilder("r")->select("AVG(r.tempsPreparation)")->getQuery()->getSingleScalarResult();
        $prepMoyenne = is_numeric($avgPrepScalar) ? round((float) $avgPrepScalar, 2) : 0.0;

        $diffMap = $this->recetteRepository->countPublishedByDifficulty();
        $segments = [];
        foreach ($diffMap as $diffKey => $count) {
            $segments[] = $diffKey.':'.((int) $count);
        }
        $diffLine = implode('; ', $segments);

        $byCatCounts = $this->recetteRepository->countPublishedByCategories();

        $authorCounts = $this->recetteRepository->countPublishedRecipesGroupedByAuthorId();
        $userRepo = $this->registry->getRepository(User::class);
        $pseudoById = [];
        foreach ($userRepo->findAll() as $u) {
            if ($u instanceof User && null !== $u->getId()) {
                $pseudoById[$u->getId()] = (string) ($u->getPseudo() ?? "");
            }
        }

        $auteursPlus = [];
        foreach ($authorCounts as $uid => $c) {
            $auteursPlus[] = [$pseudoById[(int) $uid] ?? "Utilisateur #{$uid}", (string) ((int) $c)];
        }
        usort($auteursPlus, static fn(array $a, array $b) => ((int) $b[1]) <=> ((int) $a[1]));
        $auteursPlus = array_slice($auteursPlus, 0, 3);

        $io->title("RecipeHub");

        $io->section("Vue generale");
        $io->table(
            ["Indicateur", "Valeur"],
            [
                ["Recettes publiees", (string) $pub],
                ["Brouillons", (string) $brouillon],
                ["Ingredients au total", (string) $ingredientTotal],
                ["Temps de preparation moyen", "{$prepMoyenne} min"],
                ["Publication / difficulte", $diffLine],
            ]
        );

        if ($input->getOption("detail")) {
            $rows = [["Categorie publie", "Nombre"]];
            foreach ($this->categorieRepository->findAll() as $c) {
                if (!$c instanceof CategorieRecette || null === $c->getId()) {
                    continue;
                }
                $label = trim((string) ($c->getIcone() ?? "") . " " . trim((string) $c->getNom()));
                $rows[] = [$label, (string) ($byCatCounts[(string) $c->getId()] ?? 0)];
            }
            $io->section("Par categorie (publiees)");
            $io->table(["Categorie publie", "Nombre"], array_slice($rows, 1));
        }

        $topOpt = trim((string) ($input->getOption("top") ?? ""));
        if ($topOpt !== "" && ctype_digit($topOpt)) {
            $n = (int) $topOpt;
            $all = $this->recetteRepository->findAll();
            usort($all, fn(Recette $a, Recette $b) => $this->tempsTotal($b) <=> $this->tempsTotal($a));
            $slice = array_slice($all, 0, $n);
            $topRows = [["Titre", "Temps total (min)"]];
            foreach ($slice as $r) {
                if ($r instanceof Recette) {
                    $topRows[] = [(string) $r->getTitre(), (string) $this->tempsTotal($r)];
                }
            }
            $io->section("Top temps total preparation + cuisson");
            array_shift($topRows);
            $io->table(["Titre", "Temps total (min)"], $topRows);
        }

        $io->section("Top 3 auteurs prolifiques (recettes publiees)");
        $hdr = [["Pseudo", "Recettes pub."]];
        $io->table($hdr[0], $auteursPlus);

        $io->success("Termine.");

        return Command::SUCCESS;
    }
}
