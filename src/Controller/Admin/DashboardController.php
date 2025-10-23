<?php

namespace App\Controller\Admin;

use App\Entity\CategoryRecipe;
use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeStep;
use App\Entity\Unit;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        return $this->redirectToRoute('admin_recipe_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Mon site de recettes');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToUrl('Accueil', 'fa fa-home', '/'),
            MenuItem::section('Recettes'),
            MenuItem::linkToCrud('Recettes', 'fas fa-list', Recipe::class),
            MenuItem::linkToCrud('Catégories', 'fas fa-tags', CategoryRecipe::class),
            MenuItem::linkToCrud('Ingrédients', 'fas fa-list', Ingredient::class),
            MenuItem::linkToCrud('Unités', 'fas fa-list', Unit::class)
        ];
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
