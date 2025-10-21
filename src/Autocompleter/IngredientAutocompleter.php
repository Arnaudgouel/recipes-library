<?php

namespace App\Autocompleter;

use App\Entity\Ingredient;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\UX\Autocomplete\EntityAutocompleterInterface;

#[AutoconfigureTag('ux.entity_autocompleter', ['alias' => 'ingredient'])]
class IngredientAutocompleter implements EntityAutocompleterInterface
{
    public function getEntityClass(): string
    {
        return Ingredient::class;
    }

    public function createFilteredQueryBuilder(EntityRepository $repository, string $query): QueryBuilder
    {
        return $repository->createQueryBuilder('i')
            ->innerJoin('App\Entity\RecipeIngredient', 'ri', 'WITH', 'ri.ingredient = i')
            ->groupBy('i.id')
            ->orderBy('i.name', 'ASC')
            ->andWhere('LOWER(i.name) LIKE :search')
            ->setParameter('search', '%'.strtolower($query).'%');
    }

    public function getLabel(object $entity): string
    {
        return $entity->getName();
    }

    public function getValue(object $entity): string
    {
        return $entity->getId();
    }

    public function isGranted(Security $security): bool
    {
        // see the "security" option for details
        return true;
    }
}