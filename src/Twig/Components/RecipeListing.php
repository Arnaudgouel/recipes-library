<?php

namespace App\Twig\Components;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Repository\RecipeRepository;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveResponder;

#[AsLiveComponent()]
class RecipeListing
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true)]
    public ?string $search = null;

    #[LiveProp(writable: true)]
    public ?int $minServings = null;

    #[LiveProp(writable: true)]
    public ?int $maxServings = null;

    #[LiveProp(writable: true)]
    public ?int $maxPrepTime = null;

    #[LiveProp(writable: true)]
    public ?int $maxCookTime = null;

    #[LiveProp(writable: true)]
    public array $selectedIngredients = [];

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public int $perPage = 12;

    #[LiveProp(writable: true)]
    public string $sortBy = 'title';

    #[LiveProp(writable: true)]
    public string $sortOrder = 'ASC';

    #[LiveProp(writable: true)]
    public ?int $targetPage = null;

    #[LiveProp(writable: true)]
    public int $renderKey = 0;  

    public function __construct(
        private RecipeRepository $recipeRepository,
        private IngredientRepository $ingredientRepository,
        private EntityManagerInterface $entityManager,
        private LiveResponder $liveResponder
    ) {

        $this->minServings = $this->getServingsOptions()[0];
        $this->maxServings = $this->getServingsOptions()[count($this->getServingsOptions()) - 1];
    }

    public function getRecipes(): array
    {
        $qb = $this->recipeRepository->createQueryBuilder('r')
            ->leftJoin('r.recipeIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'i')
            ->groupBy('r.id');

        // Filtre par recherche textuelle
        if (!empty($this->search)) {
            $qb->andWhere('r.title LIKE :search OR r.description LIKE :search OR i.name LIKE :search')
               ->setParameter('search', '%' . $this->search . '%');
        }

        // Filtre par nombre de portions
        if ($this->minServings !== null) {
            $qb->andWhere('r.servings >= :minServings')
               ->setParameter('minServings', $this->minServings);
        }

        if ($this->maxServings !== null) {
            $qb->andWhere('r.servings <= :maxServings')
               ->setParameter('maxServings', $this->maxServings);
        }

        // Filtre par temps de préparation
        if ($this->maxPrepTime !== null) {
            $qb->andWhere('r.prepMinutes <= :maxPrepTime')
               ->setParameter('maxPrepTime', $this->maxPrepTime);
        }

        // Filtre par temps de cuisson
        if ($this->maxCookTime !== null) {
            $qb->andWhere('r.cookMinutes <= :maxCookTime')
               ->setParameter('maxCookTime', $this->maxCookTime);
        }

        // Filtre par ingrédients sélectionnés
        if (!empty($this->selectedIngredients)) {
            $qb->andWhere('i.id IN (:ingredients)')
               ->setParameter('ingredients', $this->selectedIngredients);
        }

        // Tri
        $qb->orderBy('r.' . $this->sortBy, $this->sortOrder);

        // Pagination
        $qb->setFirstResult(($this->page - 1) * $this->perPage)
           ->setMaxResults($this->perPage);

        return $qb->getQuery()->getResult();
    }

    public function getTotalRecipes(): int
    {
        $qb = $this->recipeRepository->createQueryBuilder('r')
            ->leftJoin('r.recipeIngredients', 'ri')
            ->leftJoin('ri.ingredient', 'i')
            ->select('COUNT(DISTINCT r.id)');

        // Appliquer les mêmes filtres que pour getRecipes()
        if (!empty($this->search)) {
            $qb->andWhere('r.title LIKE :search OR r.description LIKE :search OR i.name LIKE :search')
               ->setParameter('search', '%' . $this->search . '%');
        }

        if ($this->minServings !== null) {
            $qb->andWhere('r.servings >= :minServings')
               ->setParameter('minServings', $this->minServings);
        }

        if ($this->maxServings !== null) {
            $qb->andWhere('r.servings <= :maxServings')
               ->setParameter('maxServings', $this->maxServings);
        }

        if ($this->maxPrepTime !== null) {
            $qb->andWhere('r.prepMinutes <= :maxPrepTime')
               ->setParameter('maxPrepTime', $this->maxPrepTime);
        }

        if ($this->maxCookTime !== null) {
            $qb->andWhere('r.cookMinutes <= :maxCookTime')
               ->setParameter('maxCookTime', $this->maxCookTime);
        }

        if (!empty($this->selectedIngredients)) {
            $qb->andWhere('i.id IN (:ingredients)')
               ->setParameter('ingredients', $this->selectedIngredients);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalRecipes() / $this->perPage);
    }

    public function getAvailableIngredients(): array
    {
        return $this->ingredientRepository->createQueryBuilder('i')
            ->innerJoin('App\Entity\RecipeIngredient', 'ri', 'WITH', 'ri.ingredient = i')
            ->groupBy('i.id')
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getServingsOptions(): array
    {
        $result = $this->recipeRepository->createQueryBuilder('r')
            ->select('DISTINCT r.servings')
            ->groupBy('r.servings')
            ->orderBy('r.servings', 'ASC')
            ->getQuery()
            ->getArrayResult();
        
        return array_map(function($item) {
            return $item['servings'];
        }, $result);
    }

    public function getTimeOptions(): array
    {
        return [
            15 => '15 min',
            30 => '30 min',
            45 => '45 min',
            60 => '1h',
            90 => '1h30',
            120 => '2h',
            180 => '3h',
            240 => '4h',
        ];
    }

    public function getSortOptions(): array
    {
        return [
            'title' => 'Titre',
            'servings' => 'Nombre de portions',
            'prepMinutes' => 'Temps de préparation',
            'cookMinutes' => 'Temps de cuisson',
            'createdAt' => 'Date de création',
        ];
    }

    public function getTotalTime(Recipe $recipe): ?int
    {
        $prepTime = $recipe->getPrepMinutes() ?? 0;
        $cookTime = $recipe->getCookMinutes() ?? 0;
        
        return ($prepTime + $cookTime) > 0 ? $prepTime + $cookTime : null;
    }

    public function formatTime(?int $minutes): string
    {
        if ($minutes === null) {
            return 'Non spécifié';
        }

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intval($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours . 'h';
        }

        return $hours . 'h' . $remainingMinutes;
    }

    public function getPaginationPages(): array
    {
        $totalPages = $this->getTotalPages();
        $currentPage = $this->page;
        
        $pages = [];
        
        // Toujours afficher la première page
        $pages[] = 1;
        
        // Calculer les pages autour de la page courante
        $start = max(2, $currentPage - 2);
        $end = min($totalPages - 1, $currentPage + 2);
        
        // Ajouter les pages autour de la page courante
        for ($i = $start; $i <= $end; $i++) {
            if (!in_array($i, $pages)) {
                $pages[] = $i;
            }
        }
        
        // Toujours afficher la dernière page
        if ($totalPages > 1) {
            $pages[] = $totalPages;
        }
        
        return array_unique($pages);
    }

    #[LiveAction]
    public function resetFilters(): void
    {
        $this->dispatchBrowserEvent('filters:reset');
        $this->search = null;
        $this->minServings = $this->getServingsOptions()[0];
        $this->maxServings = $this->getServingsOptions()[count($this->getServingsOptions()) - 1];
        $this->maxPrepTime = null;
        $this->maxCookTime = null;
        $this->selectedIngredients = [];
        $this->page = 1;
    }

    #[LiveAction]
    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    #[LiveAction]
    public function nextPage(): void
    {
        if ($this->page < $this->getTotalPages()) {
            $this->page++;
        }
    }

    #[LiveAction]
    public function goToPage(): void
    {
        if ($this->targetPage !== null) {
            $totalPages = $this->getTotalPages();
            if ($this->targetPage >= 1 && $this->targetPage <= $totalPages) {
                $this->page = $this->targetPage;
            }
            $this->targetPage = null; // Reset after use
        }
    }
}
