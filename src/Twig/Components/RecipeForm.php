<?php

namespace App\Twig\Components;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Entity\RecipeStep;
use App\Form\RecipeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
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

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

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

    #[LiveAction]
    public function save(): ?Response
    {
        $this->submitForm();
        
        $form = $this->getForm();
        
        if ($form->isValid()) {
            $recipe = $form->getData();
            
            $this->entityManager->persist($recipe);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Recette enregistrée avec succès.');
            
            return $this->redirectToRoute('admin_recipe_index');
        }
        
        // Retourner null pour que le composant se re-rende avec les erreurs
        return null;
    }
}

