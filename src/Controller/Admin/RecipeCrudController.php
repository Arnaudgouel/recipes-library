<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Form\RecipeIngredientType;
use App\Service\RecipeImportService;
use App\Controller\Admin\RecipeStepCrudController;
use App\Entity\CategoryRecipe;
use App\Repository\CategoryRecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeCrudController extends AbstractCrudController
{
    private CategoryRecipeRepository $categoryRecipeRepository;

    public function __construct(CategoryRecipeRepository $categoryRecipeRepository)
    {
        $this->categoryRecipeRepository = $categoryRecipeRepository;
    }

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

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Recettes')
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle recette')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la recette')
            ->setEntityLabelInSingular('Recette')
            ->setEntityLabelInPlural('Recettes')
        ;
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
        yield TextareaField::new('description', 'Description')->setColumns(12)->hideOnIndex();
        yield AssociationField::new('category', 'Catégorie')->setColumns(12)->autocomplete();
        yield ChoiceField::new('season', 'Saisons')
            ->setChoices(Recipe::getAvailableSeasons())
            ->allowMultipleChoices()
            ->setColumns(12)
            ->hideOnIndex();
        yield ImageField::new('image', 'Image')
            ->setUploadedFileNamePattern('[year][month][day]-[slug]_[uuid].[extension]')
            ->setUploadDir('public/uploads/recipes-images')
            ->setBasePath('uploads/recipes-images')
            ->hideOnIndex();
        yield FormField::addRow();
        yield IntegerField::new('servings', 'Nombre de personnes')->setColumns(2)->hideOnIndex();
        yield IntegerField::new('prepMinutes', 'Temps de préparation (minutes)')->setColumns(5)->hideOnIndex();
        yield IntegerField::new('cookMinutes', 'Temps de cuisson (minutes)')->setColumns(5)->hideOnIndex();
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
        yield DateTimeField::new('updatedAt', 'Date de modification')->onlyOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        $importAction = Action::new('import', 'Importer CSV')
            ->setIcon('fa fa-upload')
            ->linkToUrl('/admin/recipe-import')

            ->setCssClass('btn btn-success')
            ->createAsGlobalAction();

        $showAction = Action::new('show', 'Voir')
            ->setIcon('fa fa-eye')
            ->linkToUrl(fn(Recipe $recipe) => $this->generateUrl('app_recipe_show', ['id' => $recipe->getId()]))
            ->setCssClass('btn-primary bg-primary text-white');

        return $actions
            ->add(Crud::PAGE_INDEX, $importAction)
            ->add(Crud::PAGE_INDEX, $showAction)
            ->reorder(Crud::PAGE_INDEX, ['import', 'new', 'show']);
    }

    #[Route('/admin/recipe-import', name: 'admin_recipe_import', methods: ['GET', 'POST'])]
    public function import(Request $request, RecipeImportService $importService): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('csv_file');

            if (!$file) {
                $this->addFlash('error', 'Veuillez sélectionner un fichier CSV.');
                return $this->render('admin/recipe/import.html.twig');
            }

            $result = $importService->importFromCsv($file);

            if ($result['success']) {
                $this->addFlash(
                    'success',
                    sprintf('Import réussi ! %d recette(s) importée(s) avec succès.', $result['imported'])
                );

                return $this->redirectToRoute('admin_recipe_index');
            } else {
                return $this->render('admin/recipe/import.html.twig', [
                    'errors' => $result['errors']
                ]);
            }
        }

        return $this->render('admin/recipe/import.html.twig');
    }
}
