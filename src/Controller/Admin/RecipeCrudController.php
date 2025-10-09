<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Form\RecipeIngredientType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function createEntity(string $entityFqcn): Recipe
    {
        $recipe = new Recipe();
        $recipe->setCreatedAt(new \DateTime());
        return $recipe;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setUpdatedAt(new \DateTime());
        parent::updateEntity($entityManager, $entityInstance);
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
    public function configureFields(string $pageName): iterable
    {

        yield TextField::new('title', 'Titre')->setColumns(12);
        yield TextEditorField::new('description', 'Description')->setColumns(12);
        yield ImageField::new('image', 'Image')
            ->setUploadedFileNamePattern('[year][month][day]-[slug]_[uuid].[extension]')
            ->setUploadDir('public/uploads/recipes-images')
            ->setBasePath('uploads/recipes-images');
        yield FormField::addRow();
        yield IntegerField::new('servings', 'Nombre de personnes')->setColumns(2);
        yield IntegerField::new('prepMinutes', 'Temps de préparation (minutes)')->setColumns(5);
        yield IntegerField::new('cookMinutes', 'Temps de cuisson (minutes)')->setColumns(5);
        yield CollectionField::new('recipeIngredients', 'Ingrédients')
            ->useEntryCrudForm(RecipeIngredientCrudController::class)
            ->hideOnIndex()
            ->setColumns(12);
            // ->setEntryToStringMethod(fn(Recipe $recipe): string => $recipe->getRecipeIngredients()->getIngredient()->getName())
            // ->setEntryToStringMethod('getDisplayQuantity')
        ;
        yield CollectionField::new('recipeSteps', 'Étapes')
            ->useEntryCrudForm(RecipeStepCrudController::class)
            ->hideOnIndex()
            ->setColumns(12);
    }
}
