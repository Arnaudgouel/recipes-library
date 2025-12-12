<?php

namespace App\EventListener;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Service\NormalizationService;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class NormalizationListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Recipe) {
            $this->updateRecipeNormalizedFields($entity);
        } elseif ($entity instanceof Ingredient) {
            $this->updateIngredientNormalizedFields($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Recipe) {
            // Vérifier si le titre ou la description ont changé
            if ($args->hasChangedField('title') || $args->hasChangedField('description')) {
                $this->updateRecipeNormalizedFields($entity);
            }
        } elseif ($entity instanceof Ingredient) {
            // Vérifier si le nom a changé
            if ($args->hasChangedField('name')) {
                $this->updateIngredientNormalizedFields($entity);
            }
        }
    }

    private function updateRecipeNormalizedFields(Recipe $recipe): void
    {
        if ($recipe->getTitle() !== null) {
            $recipe->setNormalizedTitle(NormalizationService::normalizeAccents($recipe->getTitle()));
        }
        if ($recipe->getDescription() !== null) {
            $recipe->setNormalizedDescription(NormalizationService::normalizeAccents($recipe->getDescription()));
        }
    }

    private function updateIngredientNormalizedFields(Ingredient $ingredient): void
    {
        if ($ingredient->getName() !== null) {
            $ingredient->setNormalizedName(NormalizationService::normalizeAccents($ingredient->getName()));
        }
    }
}

