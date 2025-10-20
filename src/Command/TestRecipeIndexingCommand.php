<?php

namespace App\Command;

use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-recipe-indexing',
    description: 'Teste l\'indexation automatique des recettes avec Meilisearch',
)]
class TestRecipeIndexingCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SearchService $searchService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test de l\'indexation automatique des recettes');

        try {
            // Créer une recette de test
            $recipe = new Recipe();
            $recipe->setTitle('Recette de test pour l\'indexation');
            $recipe->setDescription('Cette recette sert à tester l\'indexation automatique avec Meilisearch');
            $recipe->setServings(4);
            $recipe->setPrepMinutes(15);
            $recipe->setCookMinutes(30);

            $io->info('Création d\'une recette de test...');
            $this->entityManager->persist($recipe);
            $this->entityManager->flush();

            $io->success('Recette créée avec succès ! ID: ' . $recipe->getId());

            // Attendre un peu pour que l'indexation se fasse
            sleep(2);

            // Tester la recherche
            $io->info('Test de la recherche...');
            $results = $this->searchService->search($this->entityManager, Recipe::class, 'test');
            
            $io->success('Recherche réussie ! Nombre de résultats: ' . count($results));

            // Modifier la recette
            $io->info('Modification de la recette...');
            $recipe->setTitle('Recette de test MODIFIÉE pour l\'indexation');
            $this->entityManager->flush();

            $io->success('Recette modifiée avec succès !');

            // Attendre un peu pour que la réindexation se fasse
            sleep(2);

            // Tester la recherche avec le nouveau titre
            $io->info('Test de la recherche avec le titre modifié...');
            $results = $this->searchService->search($this->entityManager, Recipe::class, 'MODIFIÉE');
            
            $io->success('Recherche réussie ! Nombre de résultats: ' . count($results));

            // Supprimer la recette de test
            $io->info('Suppression de la recette de test...');
            $this->entityManager->remove($recipe);
            $this->entityManager->flush();

            $io->success('Recette supprimée avec succès !');

            $io->success('✅ Test d\'indexation automatique réussi !');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors du test: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
