<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    //    /**
    //     * @return Recipe[] Returns an array of Recipe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Recipe
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Récupère des recettes aléatoires
     * @param int $limit Nombre de recettes à retourner
     * @return Recipe[]
     */
    public function findRandom(int $limit = 6): array
    {
        // Compter le nombre total de recettes
        $total = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        if ($total === 0) {
            return [];
        }

        // Si on demande plus de recettes qu'il n'y en a, on retourne toutes les recettes
        if ($limit >= $total) {
            return $this->createQueryBuilder('r')
                ->orderBy('r.id', 'ASC')
                ->getQuery()
                ->getResult();
        }

        // Générer des IDs aléatoires
        $randomIds = [];
        $maxAttempts = $limit * 10; // Limite pour éviter une boucle infinie
        $attempts = 0;

        while (count($randomIds) < $limit && $attempts < $maxAttempts) {
            $randomId = rand(1, (int) $total);
            if (!in_array($randomId, $randomIds)) {
                $randomIds[] = $randomId;
            }
            $attempts++;
        }

        if (empty($randomIds)) {
            return [];
        }

        // Récupérer les recettes avec ces IDs
        return $this->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $randomIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère des recettes pour une saison donnée ou sans saison
     * @param string $season Clé de la saison (printemps, ete, automne, hiver)
     * @param int $limit Nombre de recettes à retourner
     * @return Recipe[]
     */
    public function findBySeason(string $season, int $limit = 6): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.seasons', 's')
            ->where('s.name = :season OR s.id IS NULL')
            ->setParameter('season', $season)
            ->orderBy('r.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
