<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Form\RecipeIngredientType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
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

        yield TextField::new('title');
        yield TextEditorField::new('description');
        yield ImageField::new('image')
            ->setUploadedFileNamePattern('[year]/[month]/[day]/[slug]-[uuid].[extension]')
            ->setUploadDir('public/uploads/recipes-images');
        yield IntegerField::new('servings');
        yield IntegerField::new('prepMinutes');
        yield IntegerField::new('cookMinutes');
        yield CollectionField::new('recipeIngredients')
            ->setEntryType(RecipeIngredientType::class)
            ->hideOnIndex()
            // ->setEntryToStringMethod(fn(Recipe $recipe): string => $recipe->getRecipeIngredients()->getIngredient()->getName())
            // ->setEntryToStringMethod('getDisplayQuantity')
        ;
    }
}
