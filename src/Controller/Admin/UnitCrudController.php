<?php

namespace App\Controller\Admin;

use App\Entity\Unit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UnitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Unit::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('code', 'Code'),
            TextField::new('label', 'Intitulé'),
            TextField::new('pluralLabel', 'Intitulé au pluriel'),
            TextField::new('kind', 'Type'),
        ];
    }
    
}
