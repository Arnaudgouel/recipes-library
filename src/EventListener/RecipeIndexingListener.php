<?php

namespace App\EventListener;

use App\Entity\Recipe;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;
use Psr\Log\LoggerInterface;

class RecipeIndexingListener
{
    public function __construct(
        private SearchService $searchService,
        private LoggerInterface $logger
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        
        if (!$entity instanceof Recipe) {
            return;
        }

        try {
            $this->searchService->index($entityManager, $entity);
            $this->logger->info('Recette indexée avec succès après création', [
                'recipe_id' => $entity->getId(),
                'recipe_title' => $entity->getTitle()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de l\'indexation de la recette après création', [
                'recipe_id' => $entity->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        
        if (!$entity instanceof Recipe) {
            return;
        }

        try {
            $this->searchService->index($entityManager, $entity);
            $this->logger->info('Recette réindexée avec succès après modification', [
                'recipe_id' => $entity->getId(),
                'recipe_title' => $entity->getTitle()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la réindexation de la recette après modification', [
                'recipe_id' => $entity->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();
        
        if (!$entity instanceof Recipe) {
            return;
        }

        try {
            $this->searchService->remove($entityManager, $entity);
            $this->logger->info('Recette supprimée de l\'index avec succès', [
                'recipe_id' => $entity->getId(),
                'recipe_title' => $entity->getTitle()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la suppression de la recette de l\'index', [
                'recipe_id' => $entity->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }
}
