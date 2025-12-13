<?php

namespace App\Twig\Components;

use App\Entity\Ingredient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent]
class NewIngredientForm
{
    use ComponentToolsTrait;
    use DefaultActionTrait;
    use ValidatableComponentTrait;

    #[LiveProp(writable: true)]
    #[NotBlank(message: 'Le nom est obligatoire')]
    public string $name = '';

    #[LiveAction]
    public function saveIngredient(EntityManagerInterface $entityManager): void
    {
        $this->validate();

        $ingredient = new Ingredient();
        $ingredient->setName($this->name);
        $entityManager->persist($ingredient);
        $entityManager->flush();

        $this->dispatchBrowserEvent('modal:close');
        $this->emit('ingredient:created', [
            'ingredient' => $ingredient->getId(),
        ]);

        // Reset le formulaire
        $this->name = '';
        $this->resetValidation();
    }
}

