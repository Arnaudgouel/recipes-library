<?php

namespace App\Controller\Admin;

use App\Entity\Ingredient;
use App\Entity\RecipeIngredient;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RecipeIngredientCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $em) {}

    public static function getEntityFqcn(): string
    {
        return RecipeIngredient::class;
    }


    public function configureFields(string $pageName): iterable
    {

        $ingredientRepository = $this->em->getRepository(Ingredient::class);

        yield IdField::new('id')
            ->hideOnForm();
        yield AssociationField::new('ingredient')
            ->setQueryBuilder(fn() => $ingredientRepository->createQueryBuilder('i')->orderBy('i.name', 'ASC'))
            ->setSortProperty('name')
            ->setColumns(12);
        yield IntegerField::new('position')
            ->setLabel('Ordre d\'affichage')
            ->setHelp('1 = premier ingrédient, 2 = deuxième ingrédient, etc.')
            ->setColumns(12);
        yield FormField::addRow();
        yield NumberField::new('quantity')
            ->setLabel('Quantité')
            ->setHelp('Quantité de l\'ingrédient')
            ->setColumns('col-12 col-md-5');
        yield AssociationField::new('unit')
            ->setLabel('Unité')
            ->setHelp('Unité de l\'ingrédient')
            ->setColumns('col-12 col-md-5 offset-md-1 offset-0 offset-lg-2');
        yield TextareaField::new('note')
            ->setLabel('Note')
            ->setHelp('Note pour l\'ingrédient');
    }
}
