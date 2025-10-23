<?php

namespace App\Controller\Admin;

use App\Entity\CategoryRecipe;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class CategoryRecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CategoryRecipe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Catégories de recettes')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle catégorie')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la catégorie')
            ->setEntityLabelInSingular('Catégorie')
            ->setEntityLabelInPlural('Catégories')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();
        
        yield TextField::new('name', 'Nom de la catégorie')
            ->setRequired(true)
            ->setColumns(12);
        
        yield CollectionField::new('recipes', 'Recettes')
            ->onlyOnDetail()
            ->setTemplatePath('admin/fields/recipes_collection.html.twig');
    }
}
