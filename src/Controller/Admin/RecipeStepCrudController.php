<?php

namespace App\Controller\Admin;

use App\Entity\RecipeStep;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RecipeStepCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RecipeStep::class;
    }


    public function configureFields(string $pageName): iterable
    {

        yield IntegerField::new('position', 'Ordre d\'affichage')
            ->setColumns(6);
        yield IntegerField::new('durationMin', 'Durée (minutes)')
            ->setColumns(6);
        yield TextareaField::new('instruction', 'Instruction');
    }
}
