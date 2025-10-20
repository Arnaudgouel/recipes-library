<?php

namespace App\Twig\Components;

use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Meilisearch\Bundle\SearchService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
class RecipeSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    public function __construct(
      private EntityManagerInterface $entityManager,
      private SearchService $searchService,
      private RequestStack $requestStack
    )
    {
    }

    public function getResult(): array
    {
        if (empty($this->query)) {
            return [];
        }

        try {
            dd($this->searchService->search($this->entityManager, Recipe::class, $this->query));
            return $this->searchService->search($this->entityManager, Recipe::class, $this->query);
        } catch (\Exception $e) {
            // En cas d'erreur Meilisearch, retourner un tableau vide
            return [];
        }
    }
}