<?php

namespace App\Twig\Components;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Form\RecipeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent]
class RecipeForm extends AbstractController
{
    use DefaultActionTrait;
    use LiveCollectionTrait;
    use ComponentToolsTrait;

    #[LiveProp(fieldName: 'formData')]
    public ?Recipe $recipe = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RecipeType::class, $this->recipe);
    }

    #[LiveListener('ingredient:created')]
    public function onIngredientCreated(#[LiveArg] Ingredient $ingredient): void
    {
        // L'ingrédient créé sera automatiquement disponible dans le select
        // On force un re-render du formulaire
    }
}

