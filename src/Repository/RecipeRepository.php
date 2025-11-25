<?php

namespace App\Repository;

use App\Entity\Recipe;
use App\Service\SeasonService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly SeasonService $seasonService
    ) {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * Récupère des recettes aléatoires
     * @param int $limit Nombre de recettes à récupérer
     * @return Recipe[]
     */
    public function findRandomRecipes(int $limit = 4): array
    {
        // Récupérer tous les IDs
        $allIds = $this->createQueryBuilder('r')
            ->select('r.id')
            ->getQuery()
            ->getSingleColumnResult();

        if (empty($allIds)) {
            return [];
        }

        // Faire un random en PHP
        shuffle($allIds);
        $selectedIds = array_slice($allIds, 0, $limit);

        // Récupérer les entités correspondantes
        return $this->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère des recettes de la saison courante
     * Si une recette n'a aucune saison, elle est considérée comme disponible pour toutes les saisons
     * @param int $limit Nombre de recettes à récupérer
     * @return Recipe[]
     */
    public function findRecipesByCurrentSeason(int $limit = 4): array
    {
        $currentSeason = $this->seasonService->getCurrentSeason();
        
        $matchingIds = $this->createQueryBuilder('r')
            ->select('r.id')
            ->where('r.season IS NULL OR r.season LIKE :season')
            ->setParameter('season', '%' . $currentSeason . '%')
            ->getQuery()
            ->getSingleColumnResult();
        
        if (empty($matchingIds)) {
            return [];
        }
        
        // Faire un shuffle en PHP
        shuffle($matchingIds);
        $selectedIds = array_slice($matchingIds, 0, $limit);
        
        // Récupérer les entités correspondantes
        return $this->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $selectedIds)
            ->getQuery()
            ->getResult();
    }
}

